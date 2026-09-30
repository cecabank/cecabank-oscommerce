<?php
/**
 * cecabank_security.php hook del admin de Phoenix para Cecabank
 *
 * Muestra en el escritorio del admin (index.php) el aviso de seguridad
 * mientras el módulo de pago de Cecabank tenga configurada la clave secreta
 * antigua de 8 caracteres, esté o no habilitado el módulo. Reutiliza la
 * comprobación y los textos del módulo de pago para que no diverjan.
 *
 * Phoenix registra automáticamente los hooks de includes/hooks/admin/siteWide/,
 * así que basta con copiar este fichero: no hay nada que instalar en el admin.
 *
 * @package admin
 * @copyright Copyright 2019 Cecabank S.A.
 * @license GNU Public License V2.0
 */

  class hook_admin_siteWide_cecabank_security {
    var $version = '1.0.0';

    // Se ejecuta en la cabecera de todas las páginas del admin, antes de que
    // header.php pinte los mensajes; solo actúa en el escritorio
    function listen_injectSiteStart() {
      global $PHP_SELF, $messageStack;

      if ( basename($PHP_SELF) != 'index.php' ) {
        return;
      }

      if ( !$this->loadPaymentModule() || !cecabank::has_legacy_secret() ) {
        return;
      }

      $messageStack->add(cecabank::get_legacy_secret_warning(), 'warning');
    }

    // Carga el módulo de pago de Cecabank y sus textos para reutilizar su
    // comprobación de la clave y el HTML del aviso. Sin pasar por el autoloader,
    // que volvería a incluir el fichero de idioma si ya estuviera cargado.
    function loadPaymentModule() {
      global $language;

      if ( !class_exists('cecabank', false) ) {
        $language_file = DIR_FS_CATALOG . 'includes/languages/' . $language . '/modules/payment/cecabank.php';
        $module_file = DIR_FS_CATALOG . 'includes/modules/payment/cecabank.php';

        if ( !file_exists($module_file) ) {
          return false;
        }

        if ( !defined('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_TITLE') && file_exists($language_file) ) {
          include_once($language_file);
        }

        include_once($module_file);
      }

      return class_exists('cecabank', false);
    }

  }
?>
