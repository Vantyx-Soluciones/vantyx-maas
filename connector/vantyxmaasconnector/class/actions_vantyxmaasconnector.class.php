<?php
/**
 * Actions for Vantyx MaaS Connector
 */

class ActionsVantyxMaaSConnector
{
    /**
     * Overloading the doActions function : many hooks
     *
     * @param   array()         $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    &$object        The object to process (Invoice, order, etc...)
     * @param   string          &$action        Current action (create, edit, delete, etc...)
     * @param   HookManager     $hookmanager    The hook manager instance
     * @return  int                             0 if OK, <>0 if KO
     */
    public function doActions($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $langs;

        $contexts = explode(':', $parameters['context']);

        if (in_array('invoicecard', $contexts) && $action == 'confirm_validate') {
            // Este hook se dispara justo antes de validar.
            // Pero queremos disparar el webhook DESPUÉS de que la factura esté validada y tenga número.
        }

        return 0;
    }

    /**
     * Hook para disparar el webhook MaaS después de la validación
     * 
     * @param   array()         $parameters     Hook metadatas
     * @param   CommonObject    &$object        The invoice object
     * @param   string          &$action        Current action
     * @param   HookManager     $hookmanager    The hook manager instance
     * @return  int                             0 if OK, <>0 if KO
     */
    public function afterUpdate($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $db;

        if ($parameters['context'] == 'invoicecard' && $action == 'validate') {
            return $this->triggerMaaSWebhook($object);
        }

        return 0;
    }

    /**
     * Envía el payload a Vantyx MaaS Cloud
     */
    private function triggerMaaSWebhook($object)
    {
        global $conf;

        $api_url = $conf->global->VANTYXMAAS_API_URL;
        $token = $conf->global->VANTYXMAAS_TOKEN;

        if (empty($api_url) || empty($token))
            return 0;

        // Limpiar objeto para el payload
        $payload = [
            'event' => 'invoice.validate',
            'object' => $object
        ];

        $json_payload = json_encode($payload);

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Vantyx-Token: ' . $token
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Opcional: Loguear en Dolibarr si hubo error
        if ($http_code != 200) {
            dol_syslog("Vantyx MaaS Connector Error: HTTP " . $http_code . " - Response: " . $response, LOG_ERR);
        }

        return 0;
    }
}
