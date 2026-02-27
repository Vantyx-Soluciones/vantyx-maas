# GUÍA DE DESPLIEGUE: VANTYX MAAS EN AWS EC2

Esta guía detalla los pasos para instalar y configurar el motor **Vantyx Module as a Service (MaaS)** en una instancia de AWS.

## 1. Requisitos del Servidor (EC2)

- **SO:** Ubuntu 22.04 LTS o superior.
- **Stack:** Apache/Nginx + PHP 8.1+.
- **Extensiones PHP Obligatorias:**
  ```bash
  sudo apt update
  sudo apt install php-soap php-curl php-xml php-openssl php-json
  ```

## 2. Estructura de Directorios

El código debe clonarse en `/var/www/vantyx-maas/`.
La estructura final en el servidor debe verse así:

```text
/var/www/vantyx-maas/
├── core/
│   ├── index.php (Endpoint Público)
│   ├── config.php (Configuración de Clientes)
│   ├── modules/
│   │   └── arca/
│   │       ├── ArcaService.php
│   │       └── AFIPConnectorV2.php
│   └── wsdl/ (WSDLs de AFIP)
├── certs/
│   └── [CUIT]/ (Certificados .crt y .key de cada cliente)
└── logs/
    └── maas.log
```

## 3. Configuración de Seguridad (AWS Console)

En el **Security Group** de la instancia, habilitar:

1. **Puerto 80/443 (HTTP/S):** Para recibir los webhooks de Dolibarr.
2. **Puerto 22 (SSH):** Para administración.

## 4. Gestión de Certificados

Los certificados de ARCA/AFIP **no se suben al repositorio**. Deben subirse manualmente a la carpeta `/var/www/vantyx-maas/certs/[CUIT]/` usando SCP o SFTP.

- `certificado.crt`: Certificado público.
- `clave_privada.key`: Clave privada generada con OpenSSL.

## 5. Configuración de Clientes (`config.php`)

Por cada cliente MaaS, agregar una entrada en `core/config.php`:

```php
'TOKEN_UNICO_CLIENTE' => [
    'client_name' => 'Nombre Cliente',
    'cuit' => '20123456789',
    'production' => true,
    'dolibarr_url' => 'https://cliente.dolibarr.vantyx.net',
    'dolibarr_api_key' => 'SU_API_KEY_AQUI'
],
```

## 6. Verificación de Funcionamiento

Una vez desplegado, el endpoint para el webhook en Dolibarr será:
`https://tu-ip-o-dominio/core/index.php?token=TOKEN_UNICO_CLIENTE`

---

**IMPORTANTE:** Asegurarse de que el usuario del servidor web (`www-data`) tenga permisos de escritura en la carpeta `logs/` y en `core/cache/` (que se creará automáticamente para los Tickets de Acceso de AFIP).
