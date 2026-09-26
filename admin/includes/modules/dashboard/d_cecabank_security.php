<?php
/**
 * d_cecabank_security.php módulo de escritorio del admin para Cecabank
 *
 * Muestra en el escritorio del admin el aviso de seguridad mientras el módulo
 * de pago de Cecabank tenga configurada la clave secreta antigua de 8
 * caracteres, esté o no habilitado el módulo. Reutiliza la comprobación y los
 * textos del módulo de pago para que no diverjan.
 *
 * @package admin
 * @copyright Copyright 2019 Cecabank S.A.
 * @license GNU Public License V2.0
 */

  class d_cecabank_security {
    var $code = 'd_cecabank_security';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_cecabank_security() {
      $this->title = MODULE_ADMIN_DASHBOARD_CECABANK_SECURITY_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_CECABANK_SECURITY_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_CECABANK_SECURITY_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_CECABANK_SECURITY_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_CECABANK_SECURITY_STATUS == 'True');
      }
    }

    function getOutput() {
      return cecabank::get_legacy_secret_warning();
    }

    // Solo se pinta cuando la clave configurada es la antigua de 8 caracteres
    function isEnabled() {
      return ($this->enabled && $this->loadPaymentModule() && cecabank::has_legacy_secret());
    }

    // Carga el módulo de pago de Cecabank y sus textos para reutilizar su
    // comprobación de la clave y el HTML del aviso
    function loadPaymentModule() {
      global $language;

      $language_file = DIR_FS_CATALOG_LANGUAGES . $language . '/modules/payment/cecabank.php';
      $module_file = DIR_FS_CATALOG_MODULES . 'payment/cecabank.php';

      if ( !defined('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_TITLE') && file_exists($language_file) ) {
        include_once($language_file);
      }

      if ( !class_exists('cecabank') && file_exists($module_file) ) {
        include_once($module_file);
      }

      return class_exists('cecabank');
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_CECABANK_SECURITY_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Habilitar aviso de seguridad de Cecabank', 'MODULE_ADMIN_DASHBOARD_CECABANK_SECURITY_STATUS', 'True', '¿Mostrar en el escritorio el aviso de actualización de seguridad cuando la clave secreta de Cecabank sea la antigua de 8 caracteres?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Orden', 'MODULE_ADMIN_DASHBOARD_CECABANK_SECURITY_SORT_ORDER', '50', 'Orden de visualización. El más bajo se muestra primero.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_CECABANK_SECURITY_STATUS', 'MODULE_ADMIN_DASHBOARD_CECABANK_SECURITY_SORT_ORDER');
    }
  }
?>
