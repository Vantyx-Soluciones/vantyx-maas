# GUÍA DE CONFIGURACIÓN: SERVICIOS Y PLANTILLAS VANTYX MAAS

Para formalizar la venta de los módulos empaquetados y proteger a Vantyx, seguí estos pasos en tu instancia de gestión (`http://localhost/vantyx/htdocs/`):

## 1. Crear el Servicio "Vantyx MaaS"

1. Ir a **Productos | Servicios -> Nuevo servicio**.
2. **Ref:** `SERV-MAAS-ARCA` (o similar).
3. **Etiqueta:** `Activación y Conectividad Vantyx MaaS - Factura ARCA`.
4. **Estado:** En venta.
5. **Naturaleza:** Servicio.
6. **Precio de venta:** $140.000 (o el valor acordado).
7. **Nota:** "Incluye la entrega de los módulos empaquetados, la configuración inicial de certificados en la nube Vantyx y 12 meses de servicio de conectividad."

## 2. Configurar la Plantilla de Contrato (ODT)

Para que el contrato se genere solo con los datos del cliente, usá estos "Tags" de Dolibarr en un archivo .odt:

### Datos del Acuerdo (Resumen para copiar al ODT)

- **Título:** {object_ref} - Acuerdo de Servicio Vantyx MaaS
- **Cliente:** {company_name}
- **CUIT Cliente:** {company_idprof1}
- **Fecha:** {object_date}
- **Monto:** {object_total_ht}

### Texto Legal Sugerido para la Plantilla:

> "Vantyx Soluciones ({my_company_name}) otorga a {company_name} una licencia de uso no transferible para el sistema {object_ref}. Queda prohibida la redistribución o reventa del código fuente bajo leyes de Propiedad Intelectual de la Rep. Argentina..."

## 3. Asesoría sobre la Entrega por Email

Enviar los módulos y el acuerdo por mail **es válido y te cubre legalmente** si sigues este protocolo:

1. **La Factura es la clave:** En la descripción de tu factura de Dolibarr, agregá siempre esta leyenda:
   > "El pago de la presente factura implica la aceptación total de los términos y condiciones del 'Acuerdo de Servicio Vantyx MaaS' adjunto a este envío."
2. **Propiedad Intelectual:** Al ser un "Servicio de Conectividad" (MaaS), no estás vendiendo el código, sino la **suscripción al servicio**. Esto te da el derecho legal de cortar el acceso si detectas reventa o falta de pago.
3. **Descargo de Responsabilidad:** Debe ir SIEMPRE adjunto. Te libera de problemas si Dolibarr saca una actualización que rompe algo (tú solo respondes por tu conector).

## Resumen de Documentos a Enviar al Cliente:

1. **Factura de Venta.**
2. **Acuerdo de Servicio MaaS** (Generado desde el módulo de Contratos de Dolibarr con la plantilla ODT).
3. **Descargo de Responsabilidad.**
4. **ZIP del Módulo Conector.**

¿Querés que te ayude a redactar el cuerpo del email modelo para el envío?
