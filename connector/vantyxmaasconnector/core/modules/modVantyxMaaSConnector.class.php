<?php
/**
 * Vantyx MaaS Connector - Module Descriptor
 * Standard Dolibarr module for client integration with Vantyx Cloud.
 */

include_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

class modVantyxMaaSConnector extends DolibarrModules
{
    public function __construct($db)
    {
        global $langs, $conf;

        $this->db = $db;
        $this->numero = 500299;
        $this->rights_class = 'vantyxmaasconnector';
        $this->family = "interface";
        $this->module_position = '95';
        $this->name = 'vantyxmaasconnector';
        $this->description = "Conector Vantyx MaaS (Cloud Services)";
        $this->descriptionlong = "Permite conectar su instancia de Dolibarr con los servicios en la nube de Vantyx Soluciones (Facturación ARCA, IA, etc.) de forma segura.";
        $this->editor_name = 'Vantyx Soluciones';
        $this->editor_url = 'https://vantyx.net';
        $this->version = '1.0.0';
        $this->const_name = 'MAIN_MODULE_VANTYXMAASCONNECTOR';
        $this->picto = 'vantyxmaasconnector@vantyxmaasconnector';

        $this->module_parts = [
            'admin' => 1,
            'hooks' => [
                'data' => ['invoicecard'],
                'entity' => '0',
            ],
        ];

        $this->config_page_url = ['setup.php@vantyxmaasconnector'];
        $this->langfiles = ["vantyxmaasconnector@vantyxmaasconnector"];
        $this->rights = [];
        $this->menu = [];
    }

    public function init($options = '')
    {
        $sql = [];
        // Registro de constantes base
        dolibarr_set_const($this->db, 'VANTYXMAAS_API_URL', 'https://api.vantyx.net/v1/index.php', 'chaine', 0, '', 0);

        return $this->_init($sql, $options);
    }

    public function remove($options = '')
    {
        return $this->_remove([], $options);
    }
}
