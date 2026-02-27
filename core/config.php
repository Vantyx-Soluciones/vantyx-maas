<?php
/**
 * Vantyx MaaS Core API - Configuration
 */

return [
    'api_keys' => [
        'VAN-TEST-123' => [
            'client_name' => 'Cliente de Prueba',
            'cuit' => '20304050607',
            'dolibarr_url' => 'https://demo-cliente.vantyx.net/api/index.php',
            'dolibarr_api_key' => 'CLIENT_DOLIBARR_KEY_HERE',
        ],
        // Se agregarán tokens reales aquí
    ],
    'certs_path' => __DIR__ . '/../certs/',
    'log_path' => __DIR__ . '/../logs/maas.log',
];
