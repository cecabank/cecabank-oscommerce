# Cecabank Addon OsCommerce

# Más información

Para tener más información de como instalar el plugin de Cecabank en OsCommerce puede ver el manual de este plugin en la sección Ayuda y Recursos de la consola de Cecabank.

# Descripción

El addon de Cecabank para OsCommerce permite realizar cobros a tus clientes utilizando el TPV de Cecabank.

Compatible con osCommerce Online Merchant 2.3.x (la rama `phoenix` contiene la versión para Phoenix).

# Instalación manual

El método de instalación manual se refiere a descargar nuestro plugin y subirlo vía ftp a su servidor.

1. Sube los directorios `includes`, `ext` y `admin` a la raíz de la tienda, respetando la estructura (si has renombrado el directorio `admin`, sube el contenido de `admin/` a tu directorio de administración). Ficheros que se añaden:
   - `includes/modules/payment/cecabank.php` y `includes/modules/payment/cecabank_lib/` (módulo de pago y librería)
   - `includes/languages/espanol/modules/payment/cecabank.php` y `includes/languages/english/modules/payment/cecabank.php` (textos del módulo)
   - `ext/modules/payment/cecabank/notify.php` (notificación de pago)
   - `admin/includes/modules/dashboard/d_cecabank_security.php` y `admin/includes/languages/{espanol,english}/modules/dashboard/d_cecabank_security.php` (módulo de escritorio con el aviso de seguridad)
2. En el admin, entra en Módulos > Pago > Instalar módulo, selecciona Cecabank e instálalo. Edítalo para indicar Merchant ID, Acquirer, Terminal ID, Clave Secreta y Real o Prueba.
3. En el admin, entra en Módulos > Dashboard > Instalar módulo, selecciona "Cecabank: aviso de seguridad" e instálalo. Este módulo solo pinta algo en el escritorio mientras la clave secreta configurada sea la antigua de 8 caracteres.

# Aviso de actualización de seguridad

Cecabank ha sustituido la clave secreta de 8 caracteres (firma SHA2) por una de 32 caracteres (firma HMAC). El módulo decide el tipo de firma por la longitud de la clave (`cecabank::is_legacy_secret()` en `includes/modules/payment/cecabank.php`) y, con esa misma comprobación, muestra el aviso "Acción requerida: actualización de seguridad pendiente" en dos sitios mientras la clave configurada tenga 8 caracteres, esté o no habilitado el módulo:

- Escritorio del admin (módulo de dashboard `d_cecabank_security`).
- Módulos > Pago, al seleccionar Cecabank (en la descripción del módulo).

Los textos están en los ficheros de idioma del módulo. El enlace al manual del plugin se configura en la constante `URL_PLUGIN_MANUAL` de la clase `cecabank`; mientras esté vacía, "Manual plugin" se muestra sin enlace.

# Uso

Para utilizar el plugin usted necesita tener acceso a un TPV de Cecabank para poder obtener las credenciales.
