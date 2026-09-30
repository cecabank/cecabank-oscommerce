# Cecabank Addon OsCommerce (CE Phoenix)

# Más información

Para tener más información de como instalar el plugin de Cecabank en OsCommerce puede ver el manual de este plugin en la sección Ayuda y Recursos de la consola de Cecabank.

# Descripción

El addon de Cecabank para OsCommerce permite realizar cobros a tus clientes utilizando el TPV de Cecabank.

Esta rama es la versión para CE Phoenix 1.0.x con la estructura clásica de módulos (hasta la 1.0.5.x: `includes/classes/order.php` y carga manual de los módulos de pago). La rama `master` contiene la versión para osCommerce 2.3.x.

# Instalación manual

El método de instalación manual se refiere a descargar nuestro plugin y subirlo vía ftp a su servidor.

1. Sube los directorios `includes` y `ext` a la raíz de la tienda, respetando la estructura. Ficheros que se añaden:
   - `includes/modules/payment/cecabank.php` y `includes/modules/payment/cecabank_lib/` (módulo de pago y librería)
   - `includes/languages/espanol/modules/payment/cecabank.php` y `includes/languages/english/modules/payment/cecabank.php` (textos del módulo)
   - `includes/hooks/admin/siteWide/cecabank_security.php` (hook del admin con el aviso de seguridad en el escritorio; Phoenix lo registra automáticamente, no hay que instalarlo)
   - `ext/modules/payment/cecabank/notify.php` (notificación de pago)
2. En el admin, entra en Modules > Payment > Install Module, selecciona Cecabank e instálalo. Edítalo para indicar Merchant ID, Acquirer, Terminal ID, Clave Secreta y Real o Prueba.

# Aviso de actualización de seguridad

Cecabank ha sustituido la clave secreta de 8 caracteres (firma SHA2) por una de 32 caracteres (firma HMAC). El módulo decide el tipo de firma por la longitud de la clave (`cecabank::is_legacy_secret()` en `includes/modules/payment/cecabank.php`) y, con esa misma comprobación, muestra el aviso "Acción requerida: actualización de seguridad pendiente" en dos sitios mientras la clave configurada tenga 8 caracteres, esté o no habilitado el módulo:

- Escritorio del admin (hook `includes/hooks/admin/siteWide/cecabank_security.php`, que lo añade a los mensajes del admin).
- Modules > Payment, al seleccionar Cecabank (en la descripción del módulo).

Los textos están en los ficheros de idioma del módulo. El enlace al manual del plugin se configura en la constante `URL_PLUGIN_MANUAL` de la clase `cecabank`; mientras esté vacía, "Manual plugin" se muestra sin enlace.

# Uso

Para utilizar el plugin usted necesita tener acceso a un TPV de Cecabank para poder obtener las credenciales.
