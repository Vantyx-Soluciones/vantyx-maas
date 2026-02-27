# Arquitectura Técnica Vantyx MaaS (Module as a Service)

Este documento define cómo Vantyx distribuirá sus módulos custom (empezando por Factura ARCA) de forma segura y centralizada.

## Componentes

### 1. Vantyx MaaS Core (AWS EC2)

- **Tecnología:** PHP/Slim o Node.js/Express.
- **Responsabilidad:**
  - Almacenamiento seguro de certificados .crt y .key organizados por CUIT.
  - Autenticación de clientes mediante API Tokens.
  - Orquestación con AFIP (consumo de WebServices).
  - Callback REST para inyectar resultados en el Dolibarr del cliente.

### 2. Vantyx MaaS Connector (Módulo Cliente)

- **Tecnología:** Peso pluma (Dolibarr Custom Module).
- **Responsabilidad:**
  - Pantalla de configuración para el Token de Vantyx.
  - Instrucciones/Setup de Webhooks nativos de Dolibarr.
  - Recibir y mostrar logs de conexión con la nube Vantyx.

## Flujo de Datos

1. Cliente valida factura en Dolibarr.
2. Webhook nativo envía JSON a `api.vantyx.net/v1/facturaarca`.
3. Vantyx Core valida el Token y busca el certificado del CUIT emisor.
4. Vantyx Core obtiene CAE de AFIP.
5. Vantyx Core actualiza la factura del cliente mediante API REST nativa (`PUT /invoices/{id}`).

## Modelo de Negocio

- **Setup Fee:** Pago inicial por configuración de certificados y vinculación inicial (Ej. $140.000).
- **Maintenance (SaaS):** Abono mensual/anual por el uso de la infraestructura de nube y actualizaciones automáticas de leyes/ARCA.
