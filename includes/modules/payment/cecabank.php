<?php
/**
 * cecabank.php modulo de pago para Cecabank
 *
 * @package paymentMethod
 * @copyright Copyright 2019 Cecabank S.A.
 * @license GNU Public License V2.0
 */

$autoloader_param = __DIR__ . '/cecabank_lib/Cecabank/Client.php';
try {
    require_once $autoloader_param;
} catch (\Exception $e) {
    throw new \Exception('Error en el plugin de Cecabank al cargar la librería.');
}


/**
 * cecabank.php modulo de pago para Cecabank
 *
 */
class cecabank {
  var $code;
  var $title;
  var $description;
  var $enabled;
  
  function cecabank() {
    global $order;
    $this->code = 'cecabank';
    $this->title = 'Cecabank';
    $this->public_title = 'Pago con tarjeta (Cecabank)';
    if (IS_ADMIN_FLAG === true && MODULE_PAYMENT_CECABANK_ENVIRONMENT != 'real') $this->title .= '<span class="alert">(prueba activa)</span>';
    $this->description = 'Permite utilizar la pasarela de Cecabank en tu sitio web.';
    $this->sort_order = MODULE_PAYMENT_CECABANK_SORT_ORDER;
    $this->enabled = ((MODULE_PAYMENT_CECABANK_STATUS == 'True') ? true : false);
    if ((int)MODULE_PAYMENT_CECABANK_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_CECABANK_ORDER_STATUS_ID;
    }

    if (is_object($order)) {
      $this->update_status();
      $config = $this-> get_client_config();
      $cecabank_client = new Cecabank\Client($config);
      $this->form_action_url = $cecabank_client->getPath();
    }
  }
  
  function update_status() {
    global $order, $db;

    if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_CECABANK_ZONE > 0) ) {
      $check_flag = false;
      $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_CECABANK_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
      while ($check = tep_db_fetch_array($check_query)) {
        if ($check['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check['zone_id'] == $order->billing['zone_id']) {
          $check_flag = true;
          break;
        }
      }

      if ($check_flag == false) {
        $this->enabled = false;
      }
    }
  }
  
  function javascript_validation() {
    return false;
  }

  function get_client_config() {
    return array(
        'Environment' => MODULE_PAYMENT_CECABANK_ENVIRONMENT,
        'MerchantID' => MODULE_PAYMENT_CECABANK_MERCHANT,
        'AcquirerBIN' => MODULE_PAYMENT_CECABANK_ACQUIRER,
        'TerminalID' => MODULE_PAYMENT_CECABANK_TERMINAL,
        'ClaveCifrado' => MODULE_PAYMENT_CECABANK_SECRET,
        'TipoMoneda' => MODULE_PAYMENT_CECABANK_CURRENCY,
        'Exponente' => '2',
        'Cifrado' => 'SHA2',
        'Idioma' => '1',
        'Pago_soportado' => 'SSL',
        'versionMod' => 'O-0.1.1'
    );
  }
  
  function selection() {
    $icon = MODULE_PAYMENT_CECABANK_ICON;
    $acquirer = MODULE_PAYMENT_CECABANK_ACQUIRER;
    if ($acquirer && $acquirer !== '0000554000' && $icon === "https://pgw.ceca.es/TPVvirtual/images/logo0000554000.gif") {
      $icon = "https://pgw.ceca.es/TPVvirtual/images/logo".$acquirer.".gif";
    }
    return array('id' => $this->code,
                 'module' => MODULE_PAYMENT_CECABANK_TITLE."<br><span style='font-weight: normal'>".MODULE_PAYMENT_CECABANK_DESCRIPTION."</span><br><img src='".$icon."'>");
  }

  function pre_confirmation_check() {
    return false;
  }

  function confirmation() {
    return false;
  }
    
  function process_button() {

    global $db, $order, $order_totals, $customer_id, $cartID;

    $config = $this-> get_client_config();
    $cecabank_client = new Cecabank\Client($config);

    $user;
    $user_id = $customer_id;
    $user_data;
    $user_age = 'NO_ACCOUNT';
    $user_info_age = '';
    $registered = '';
    $txn_activity_today = '';
    $txn_activity_year = '';
    $txn_purchase_6 = '';
    $ship_name_indicator = 'DIFFERENT';
    $name = $order->billing['firstname'].' '.$order->billing['lastname'];
    $email = $order->billing['email_address'];
    $ip = '';
    $city = $order->billing['city'];
    $country = $order->billing['country']['iso_code_2'];
    $line1 = $order->billing['street_address'];
    $postal_code = $order->billing['postcode'];
    $state = $order->billing['state'];
    $phone = preg_replace('/\D/', '', $order->customer['telephone']);
    $ship_name = $order->delivery['firstname'].' '.$order->delivery['lastname'];
    $ship_city = $order->delivery['city'];
    $ship_country = $order->delivery['country']['iso_code_2'];
    $ship_line1 = $order->delivery['street_address'];
    $ship_postal_code = $order->delivery['postcode'];
    $ship_state = $order->delivery['state'];
    $ship_indicator = 'CH_BILLING_ADDRESS';
    $delivery_time_frame = 'TWO_MORE_DAYS';
    $delivery_email = '';
    $reorder_items = 'FIRST_TIME_ORDERED';
    if ($line1 !== $ship_line1) {
        $ship_indicator = 'CH_NOT_BILLING_ADDRESS';
    }

    // ACS
    $acs = array();

    // Cardholder
    $cardholder = array();
    $add_cardholder = false;

    // Cardholder bill address
    $bill_address = array();
    $add_bill_address = false;
    if ($city) {
        $bill_address['CITY'] = $city;
        $add_bill_address = true;
    }                
    if ($country) {
        $bill_address['COUNTRY'] = $country;
        $add_bill_address = true;
    }
    if ($line1) {
        $bill_address['LINE1'] = $line1;
        $add_bill_address = true;
    }
    if ($postal_code) {
        $bill_address['POST_CODE'] = $postal_code;
        $add_bill_address = true;
    }                
    if ($state) {
        $bill_address['STATE'] = $state;
        $add_bill_address = true;
    }
    if ($add_bill_address) {
        $cardholder['BILL_ADDRESS'] = $bill_address;
        $add_cardholder = true;
    }

    // Cardholder name
    if ($name) {
        $cardholder['NAME'] = $name;
        $add_cardholder = true;
    }

    // Cardholder email
    if ($email) {
        $cardholder['EMAIL'] = $email;
        $add_cardholder = true;
    }

    if ($add_cardholder) {
        $acs['CARDHOLDER'] = $cardholder;
    }

    // Purchase
    $purchase = array();
    $add_purchase = true;

    // Purchase ship address
    $ship_address = array();
    $add_ship_address = false;
    if ($ship_city) {
        $ship_address['CITY'] = $ship_city;
        $add_ship_address = true;
    }                
    if ($ship_country) {
        $ship_address['COUNTRY'] = $ship_country;
        $add_ship_address = true;
    }
    if ($ship_line1) {
        $ship_address['LINE1'] = $ship_line1;
        $add_ship_address = true;
    }
    if ($ship_postal_code) {
        $ship_address['POST_CODE'] = $ship_postal_code;
        $add_ship_address = true;
    }                
    if ($ship_state) {
        $ship_address['STATE'] = $ship_state;
        $add_ship_address = true;
    }
    if ($add_ship_address) {
        $purchase['SHIP_ADDRESS'] = $ship_address;
        $add_purchase = true;
    }

    // Purchase mobile phone
    if ($phone) {
        $purchase['MOBILE_PHONE'] = array(
            'SUBSCRIBER' => $phone
        );
        $add_purchase = true;
    }

    if ($add_purchase) {
        $acs['PURCHASE'] = $purchase;
    }

    // Merchant risk
    $merchant_risk = array(
        'PRE_ORDER_PURCHASE_IND' => 'AVAILABLE'
    );
    if ($ship_indicator) {
        $merchant_risk['SHIP_INDICATOR'] = $ship_indicator;
    }
    if ($delivery_time_frame) {
        $merchant_risk['DELIVERY_TIMEFRAME'] = $delivery_time_frame;
    }
    if ($delivery_email) {
        $merchant_risk['DELIVERY_EMAIL_ADDRESS'] = $delivery_email;
    }
    if ($reorder_items) {
        $merchant_risk['REORDER_ITEMS_IND'] = $reorder_items;
    }
    $acs['MERCHANT_RISK_IND'] = $merchant_risk;

    // Account info
    $account_info = array(
        'SUSPICIOUS_ACC_ACTIVITY' => 'NO_SUSPICIOUS'
    );
    if ($user_age) {
        $account_info['CH_ACC_AGE_IND'] = $user_age;
        $account_info['PAYMENT_ACC_IND'] = $user_age;
    }
    if ($user_info_age) {
        $account_info['CH_ACC_CHANGE_IND'] = $user_info_age;
    }
    if ($registered) {
        $account_info['CH_ACC_CHANGE'] = $registered;
        $account_info['CH_ACC_DATE'] = $registered;
        $account_info['PAYMENT_ACC_AGE'] = $registered;
    }
    if ($txn_activity_today) {
        $account_info['TXN_ACTIVITY_DAY'] = $txn_activity_today;
    }
    if ($txn_activity_year) {
        $account_info['TXN_ACTIVITY_YEAR'] = $txn_activity_year;
    }
    if ($txn_purchase_6) {
        $account_info['NB_PURCHASE_ACCOUNT'] = $txn_purchase_6;
    }
    if ($ship_name_indicator) {
        $account_info['SHIP_NAME_INDICATOR'] = $ship_name_indicator;
    }
    $acs['ACCOUNT_INFO'] = $account_info;

    // Create transaction
    $cecabank_client->setFormHiddens(array(
        'Num_operacion' => $cartID,
        'Descripcion' => 'Pago del pedido '.$cartID,
        'Importe' => round($order->info['total'], 2),
        'URL_OK' => tep_href_link(FILENAME_CHECKOUT_PROCESS, 'referer=cecabank', 'SSL'),
        'URL_NOK' => tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'),
        'datos_acs_20' => base64_encode( str_replace( '[]', '{}', json_encode( $acs ) ) )
    ));
	  return $cecabank_client->getFormHiddens();
  }

  function before_process() {
  }

  function after_process() {
	  return false;
  }

  function output_error() {
    return false;
  }

  function update_order($order_id) {
    $new_order_status = (MODULE_PAYMENT_CECABANK_ORDER_STATUS_ID > 0 ? MODULE_PAYMENT_CECABANK_ORDER_STATUS_ID : DEFAULT_ORDERS_STATUS_ID);

    tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (int)$new_order_status . "', last_modified = now() where orders_id = '" . (int)$order_id . "'");

    $sql_data_array = array('orders_id' => (int)$order_id,
                                  'orders_status_id' => MODULE_PAYMENT_CECABANK_ORDER_STATUS_ID,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => 'Cecabank');
    tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
  }
  
	function check() {
	  if (!isset($this->_check)) {
	    $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_CECABANK_STATUS'");
	    $this->_check = tep_db_num_rows($check_query);
	  }
	  return $this->_check;
	}

  function install() {
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Habilitar módulo Cecabank', 'MODULE_PAYMENT_CECABANK_STATUS', 'True', '¿Acepta recibir pagos en Cecabank?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant ID', 'MODULE_PAYMENT_CECABANK_MERCHANT', '', 'Merchant ID', '6', '1', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Acquirer', 'MODULE_PAYMENT_CECABANK_ACQUIRER', '', 'Acquirer', '6', '1', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Terminal ID', 'MODULE_PAYMENT_CECABANK_TERMINAL', '', 'Terminal ID', '6', '1', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Clave Secreta', 'MODULE_PAYMENT_CECABANK_SECRET', '', 'Clave Secreta', '6', '1', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Título', 'MODULE_PAYMENT_CECABANK_TITLE', 'Tarjeta', 'Título', '6', '1', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Description', 'MODULE_PAYMENT_CECABANK_DESCRIPTION', 'Paga con tu tarjeta', 'Descripción', '6', '1', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Zona de pago', 'MODULE_PAYMENT_CECABANK_ZONE', '0', 'Si la zona es seleccionada, solo se puede usar este pago en esta zona.', '6', '4', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Estado de orden', 'MODULE_PAYMENT_CECABANK_ORDER_STATUS_ID', '2', 'Seleccionar el estado de la orden cuando el pago se ha realizado<br />(\'Processing\' recomendado)', '6', '6', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Order para mostrar', 'MODULE_PAYMENT_CECABANK_SORT_ORDER', '0', 'Un número lo mostrará de primero.', '6', '8', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Real o Prueba', 'MODULE_PAYMENT_CECABANK_ENVIRONMENT', 'prueba', '<strong>Real: </strong>  Para procesar transacciones reales<br/><strong>Test: </strong>Para desarrollo y prueba', '6', '25', 'tep_cfg_select_option(array(\'real\', \'test\'), ', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Moneda', 'MODULE_PAYMENT_CECABANK_CURRENCY', '978', '<strong>Euro: </strong> 978, <strong>Dolares: </strong> 840', '6', '0', 'tep_cfg_select_option(array(\'978\', \'840\'), ', now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Ícono', 'MODULE_PAYMENT_CECABANK_ICON', 'https://pgw.ceca.es/TPVvirtual/images/logo0000554000.gif', 'Ícono', '6', '1', now())");
  }
  
  function remove() {
    tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE\_PAYMENT\_CECABANK\_%'");
  }
  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
    */
  function keys() {
    $keys_list = array(
                       'MODULE_PAYMENT_CECABANK_ORDER_STATUS_ID',
                       'MODULE_PAYMENT_CECABANK_ZONE',
                       'MODULE_PAYMENT_CECABANK_ENVIRONMENT',
                       'MODULE_PAYMENT_CECABANK_MERCHANT',
                       'MODULE_PAYMENT_CECABANK_ACQUIRER',
                       'MODULE_PAYMENT_CECABANK_TERMINAL',
                       'MODULE_PAYMENT_CECABANK_SECRET',
                       'MODULE_PAYMENT_CECABANK_TITLE',
                       'MODULE_PAYMENT_CECABANK_DESCRIPTION',
                       'MODULE_PAYMENT_CECABANK_CURRENCY',
                       'MODULE_PAYMENT_CECABANK_ICON'
                        );
    
    return $keys_list;
  }

}
