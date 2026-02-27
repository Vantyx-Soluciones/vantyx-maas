<?php
/**
 * ArcaService - Servicio MaaS para Facturación ARCA
 */

require_once __DIR__ . '/AFIPConnectorV2.php';

class ArcaService
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Procesa una solicitud de autorización de comprobante
     */
    public function authorizeInvoice($invoiceData)
    {
        try {
            // 1. Instanciar Conector para el CUIT del cliente
            $afip = new AFIPConnectorV2([
                'cuit' => $this->config['cuit'],
                'production' => $this->config['production']
            ]);

            // 2. Obtener el próximo número disponible en AFIP
            $last = $afip->getLastVoucher($invoiceData['PtoVta'], $invoiceData['CbteTipo']);
            if ($last === false) {
                return [
                    'status' => 'error',
                    'message' => 'No se pudo obtener el último comprobante: ' . implode(' | ', $afip->errors)
                ];
            }

            $next = $last + 1;
            $invoiceData['CbteDesde'] = $next;
            $invoiceData['CbteHasta'] = $next;

            // 3. Solicitar CAE
            $result = $afip->createNextVoucher($invoiceData);
            if ($result === false) {
                return [
                    'status' => 'error',
                    'message' => 'Error al solicitar CAE: ' . implode(' | ', $afip->errors)
                ];
            }

            // 4. Retornar éxito
            return [
                'status' => 'success',
                'cae' => $result['CAE'],
                'cae_vto' => $result['CAEFchVto'],
                'cbte_nro' => $result['CbteNro'],
                'resultado' => $result['Resultado']
            ];

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
