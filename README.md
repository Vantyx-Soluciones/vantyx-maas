# Vantyx Module as a Service (MaaS) 🚀

![Vantyx Banner](https://vantyx.net/wp-content/uploads/2023/10/cropped-Logo-Vantyx-Soluciones-Horizontal.png)

### El núcleo de potencia para tu ecosistema Dolibarr.

**Vantyx MaaS** es una arquitectura propietaria diseñada para centralizar la lógica de negocio crítica, la facturación electrónica (ARCA/AFIP) y servicios de valor agregado en un backend robusto en AWS. Esta solución permite proteger la propiedad intelectual de **Vantyx Soluciones** mientras ofrece una conectividad ligera, escalable y segura a clientes que utilizan Dolibarr v22+.

---

## 🏗️ Arquitectura del Proyecto

El repositorio está organizado para separar la lógica del motor (Core) de la documentación de implementación:

- **`core/`**: El motor MaaS. Contiene los conectores SOAP (AFIP), servicios de orquestación y el receptor de webhooks.
- **`connector/`**: Ejemplo del conector ligero que vive en el Dolibarr del cliente.
- **`docs/`**: Guías técnicas de arquitectura, configuración y despliegue en AWS.
- **`legal/`**: Marco legal completo (Acuerdos de Servicio, Licencias de Código Fuente y Descargos).

---

## ✨ Características Principales

- **Centralización ARCA/AFIP:** Gestión de certificados y tokens (WSAA/WSFE) en un solo lugar.
- **Seguridad por Diseño:** Autenticación mediante `X-Vantyx-Token` y validación de endpoints.
- **Callback Automático:** Sincronización transparente de CAEs y resultados hacia el Dolibarr del cliente vía API REST.
- **Protección de IP:** El cliente nunca posee la lógica core, solo un conector que consume el servicio MaaS.

---

## 🚀 Inicio Rápido

### Para el Administrador (Geronimo)

1.  **Despliegue:** Consultar la [Guía de Despliegue en AWS](docs/DESPLIEGUE_AWS.md).
2.  **Configuración:** Añadir clientes en `core/config.php`.
3.  **Certificados:** Subir los archivos `.crt` y `.key` a la carpeta `/certs/[CUIT]/` siguiendo el protocolo de seguridad.

### Para el Cliente

1.  Instalar el módulo conector en Dolibarr.
2.  Configurar el Token de Vantyx proporcionado.
3.  ¡Listo! El sistema empezará a procesar vía MaaS automáticamente.

---

## ⚖️ Marco Legal y Licencia

Este software es propiedad exclusiva de **Vantyx Soluciones**.
El uso de los módulos empaquetados y el acceso al servicio MaaS están sujetos a:

- [Acuerdo de Servicio MaaS](legal/ACUERDO_SERVICIO_MAAS.md)
- [Licencia de Código Fuente](legal/LICENCIA_CODIGO_FUENTE.md)

---

## 💙 Pasión por el Negocio

_Diseñado con Pasión por el equipo de Vantyx Soluciones para potenciar tu negocio._

---

**Vantyx Soluciones** | [vantyx.net](https://vantyx.net) | Argentina
