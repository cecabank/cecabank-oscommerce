<?php
/**
 * cecabank.php textos del módulo de pago para Cecabank (español)
 *
 * @package paymentMethod
 * @copyright Copyright 2019 Cecabank S.A.
 * @license GNU Public License V2.0
 */

// Aviso de seguridad que se muestra en el admin cuando la clave secreta
// configurada es la antigua de 8 caracteres. Los %s se sustituyen por enlaces.
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_TITLE', 'Acción requerida: actualización de seguridad pendiente');
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_INTRO', 'Tu comercio está configurado con una clave de 8 caracteres. Para completar la adaptación de seguridad debes:');
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_STEP_KEY', 'Configurar la nueva clave de 32 caracteres que puedes encontrar en el %s, en la configuración de tu comercio.');
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_PORTAL', 'Portal de Administración del TPV Virtual');
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_STEP_INFO', '📖 Más información: %s %s');
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_BULLETIN', 'Boletín de Seguridad');
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_MANUAL', 'Manual plugin');
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_FOOTER', 'Si ya has completado estas actualizaciones, puedes ignorar este mensaje.');
?>
