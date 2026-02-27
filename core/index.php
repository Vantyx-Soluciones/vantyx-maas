<?php
/**
 * Vantyx MaaS Core API - Webhook Receiver
 * Receive invoice data from Dolibarr client and process with AFIP.
 */

$config = require 'config.php';

// Simple Router/Receiver
$method = $_SERVER['REQUEST_METHOD'];
$request_body = file_get_contents('php://input');
$headers = getallheaders();

// 1. Basic Auth / Token Check
$token = $headers['X-Vantyx-Token'] ?? $_GET['token'] ?? '';

if (!isset($config['api_keys'][$token])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Invalid or missing Vantyx Token']);
    exit;
}

$client_data = $config['api_keys'][$token];

// 2. Log Request
file_put_contents($config['log_path'], "[" . date('Y-m-d H:i:s') . "] Request from " . $client_data['client_name'] . " - CUIT: " . $client_data['cuit'] . "\n", FILE_APPEND);

// 3. Process Invoice Data
$data = json_decode($request_body, true);
if (!$data || !isset($data['object'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid JSON payload or missing object']);
    exit;
}

// 4. Lógica de Negocio (ARCA)
require_once __DIR__ . '/modules/arca/ArcaService.php';

$arca = new ArcaService([
    'cuit' => $client_data['cuit'],
    'production' => $client_data['production'] ?? false
]);

// Procesar autorización
$result = $arca->authorizeInvoice($data['object']);

if ($result['status'] === 'success') {
    // 5. Callback to Client Dolibarr
    // Aquí actualizamos la factura en el Dolibarr del cliente vía API REST
    $update_data = [
        'array_options' => [
            'options_vantyxfacturaarca_cae' => $result['cae'],
            'options_vantyxfacturaarca_cae_vto' => $result['cae_vto'],
            'options_vantyxfacturaarca_cbte_nro' => $result['cbte_nro'],
            'options_vantyxfacturaarca_resultado' => $result['resultado']
        ]
    ];

    $ch = curl_init($client_data['dolibarr_url'] . '/api/index.php/invoices/' . $data['object']['id']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'DOLAPIKEY: ' . $client_data['dolibarr_api_key']
    ]);
    $callback_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    file_put_contents($config['log_path'], "[" . date('Y-m-d H:i:s') . "] Callback to " . $client_data['client_name'] . " - HTTP: " . $http_code . "\n", FILE_APPEND);
}

// 6. Response to Webhook
header('Content-Type: application/json');
echo json_encode($result);
