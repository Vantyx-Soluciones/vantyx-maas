<?php
/**
 * AFIPConnectorV2 - Versión Standalone para Vantyx MaaS
 * 
 * Basado en la implementación de VantyxFacturaARCA
 * Refactorizado para funcionar fuera de Dolibarr.
 */

class AFIPConnectorV2
{
    private $cuit;
    private $production;
    private $certPath;
    private $keyPath;
    private $cacheDir;
    private $wsdlDir;

    public $errors = [];

    // Service URLs (Endpoints)
    const WSAA_URL_HOMO = 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms';
    const WSAA_URL_PROD = 'https://wsaa.afip.gov.ar/ws/services/LoginCms';
    const WSFE_URL_HOMO = 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx';
    const WSFE_URL_PROD = 'https://servicios1.afip.gov.ar/wsfev1/service.asmx';

    public function __construct($config)
    {
        $this->cuit = preg_replace('/[^0-9]/', '', $config['cuit']);
        $this->production = (bool) ($config['production'] ?? false);

        $this->wsdlDir = __DIR__ . '/../../wsdl/';
        $this->cacheDir = __DIR__ . '/../../cache/';

        // Rutas de certificados específicas por CUIT
        $this->certPath = __DIR__ . '/../../../certs/' . $this->cuit . '/certificado.crt';
        $this->keyPath = __DIR__ . '/../../../certs/' . $this->cuit . '/clave_privada.key';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }

        if (!extension_loaded('openssl') || !extension_loaded('soap')) {
            $this->errors[] = 'Faltan extensiones PHP requeridas (OpenSSL/SOAP).';
        }
    }

    private function getWSDL($service)
    {
        $base = $this->wsdlDir;
        if ($service == 'wsaa')
            return $this->production ? $base . 'wsaa_prod.wsdl' : $base . 'wsaa_homo.wsdl';
        if ($service == 'wsfe')
            return $this->production ? $base . 'wsfe_prod.wsdl' : $base . 'wsfe_homo.wsdl';
    }

    private function getEndpoint($service)
    {
        if ($service == 'wsaa')
            return $this->production ? self::WSAA_URL_PROD : self::WSAA_URL_HOMO;
        if ($service == 'wsfe')
            return $this->production ? self::WSFE_URL_PROD : self::WSFE_URL_HOMO;
    }

    private function getSoapOptions($locationUrl)
    {
        $ssl_opts = ['ciphers' => 'DEFAULT@SECLEVEL=1'];
        if (!$this->production) {
            $ssl_opts['verify_peer'] = false;
            $ssl_opts['verify_peer_name'] = false;
            $ssl_opts['allow_self_signed'] = true;
        }

        return [
            'soap_version' => SOAP_1_2,
            'exceptions' => true,
            'trace' => 1,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
            'location' => $locationUrl,
            'stream_context' => stream_context_create(['ssl' => $ssl_opts])
        ];
    }

    private function getAuth()
    {
        $xmlFile = $this->cacheDir . 'TA-' . $this->cuit . '-wsfe.xml';

        if (file_exists($xmlFile)) {
            $TA = simplexml_load_file($xmlFile);
            $expiration = strtotime($TA->header->expirationTime);
            if ($expiration > (time() + 600)) {
                return [
                    'Token' => (string) $TA->credentials->token,
                    'Sign' => (string) $TA->credentials->sign,
                    'Cuit' => (float) $this->cuit
                ];
            }
        }

        // Generate TRA
        $TRA = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><loginTicketRequest version="1.0"></loginTicketRequest>');
        $header = $TRA->addChild('header');
        $header->addChild('uniqueId', date('U'));
        $header->addChild('generationTime', date('c', date('U') - 60));
        $header->addChild('expirationTime', date('c', date('U') + 600));
        $TRA->addChild('service', 'wsfe');

        $tra_xml = $TRA->asXML();
        $tra_file = $this->cacheDir . 'TRA-' . $this->cuit . '.xml';
        $cms_file = $this->cacheDir . 'TRA-' . $this->cuit . '.tmp';

        file_put_contents($tra_file, $tra_xml);

        // Sign TRA
        $status = openssl_pkcs7_sign(
            $tra_file,
            $cms_file,
            'file://' . $this->certPath,
            ['file://' . $this->keyPath, null],
            [],
            0
        );

        if (!$status)
            throw new Exception("Error OpenSSL al firmar TRA: " . openssl_error_string());

        $infofile = file_get_contents($cms_file);
        $parts = preg_split('/(\r\n\r\n|\n\n)/', $infofile);
        $cms = str_replace(["\r", "\n", " "], "", trim($parts[1] ?? $infofile));

        // Call LoginCms
        $wsdl = $this->getWSDL('wsaa');
        $client = new SoapClient($wsdl, $this->getSoapOptions($this->getEndpoint('wsaa')));
        $results = $client->loginCms(['in0' => $cms]);
        $TA_xml = $results->loginCmsReturn;

        file_put_contents($xmlFile, $TA_xml);
        $TA = simplexml_load_string($TA_xml);

        return [
            'Token' => (string) $TA->credentials->token,
            'Sign' => (string) $TA->credentials->sign,
            'Cuit' => (float) $this->cuit
        ];
    }

    public function createNextVoucher($invoiceData)
    {
        try {
            $auth = $this->getAuth();
            $wsdl = $this->getWSDL('wsfe');
            $client = new SoapClient($wsdl, $this->getSoapOptions($this->getEndpoint('wsfe')));

            $req = [
                'Auth' => [
                    'Token' => $auth['Token'],
                    'Sign' => $auth['Sign'],
                    'Cuit' => $auth['Cuit']
                ],
                'FeCAEReq' => [
                    'FeCabReq' => [
                        'CantReg' => 1,
                        'PtoVta' => $invoiceData['PtoVta'],
                        'CbteTipo' => $invoiceData['CbteTipo']
                    ],
                    'FeDetReq' => [
                        'FECAEDetRequest' => $invoiceData
                    ]
                ]
            ];

            $result = $client->FECAESolicitar($req);
            $resp = $result->FECAESolicitarResult;

            if (isset($resp->Errors)) {
                $err = $resp->Errors->Err;
                throw new Exception("AFIP Error: " . (is_array($err) ? $err[0]->Msg : $err->Msg));
            }

            $det = is_array($resp->FeDetResp->FECAEDetResponse) ? $resp->FeDetResp->FECAEDetResponse[0] : $resp->FeDetResp->FECAEDetResponse;

            if ($det->Resultado == 'R') {
                $obs = isset($det->Observaciones) ? (is_array($det->Observaciones->Obs) ? $det->Observaciones->Obs[0]->Msg : $det->Observaciones->Obs->Msg) : 'Sin observaciones';
                throw new Exception("Factura Rechazada: " . $obs);
            }

            return [
                'CAE' => $det->CAE,
                'CAEFchVto' => $det->CAEFchVto,
                'CbteNro' => $det->CbteDesde,
                'Resultado' => $det->Resultado
            ];
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    public function getLastVoucher($ptoVta, $cbteTipo)
    {
        try {
            $auth = $this->getAuth();
            $wsdl = $this->getWSDL('wsfe');
            $client = new SoapClient($wsdl, $this->getSoapOptions($this->getEndpoint('wsfe')));

            $result = $client->FECompUltimoAutorizado([
                'Auth' => $auth,
                'PtoVta' => $ptoVta,
                'CbteTipo' => $cbteTipo
            ]);

            return (int) $result->FECompUltimoAutorizadoResult->CbteNro;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }
}
