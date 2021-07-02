<?php
/**
 * cecabank.php modulo de pago para Cecabank
 *
 * @package paymentMethod
 * @copyright Copyright 2019 Cecabank S.A.
 * @license GNU Public License V2.0
 */

chdir("../../../../");
require('includes/application_top.php');
require('includes/modules/payment/cecabank.php');
include(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_PROCESS);

$autoloader_param = 'includes/modules/payment/cecabank_lib/Cecabank/Client.php';
try {
    require_once $autoloader_param;
} catch (\Exception $e) {
    throw new \Exception('Error en el plugin de Cecabank al cargar la librería.');
}

  function get_client_config() {
    return array(
        'Environment' => MODULE_PAYMENT_CECABANK_ENVIRONMENT,
        'MerchantID' => MODULE_PAYMENT_CECABANK_MERCHANT,
        'AcquirerBIN' => MODULE_PAYMENT_CECABANK_ACQUIRER,
        'TerminalID' => MODULE_PAYMENT_CECABANK_TERMINAL,
        'ClaveCifrado' => MODULE_PAYMENT_CECABANK_SECRET,
        'Exponente' => '2',
        'Cifrado' => 'SHA2',
        'Idioma' => '1',
        'Pago_soportado' => 'SSL',
        'versionMod' => 'O-0.1.2'
    );
  }

    $config = get_client_config();
    $cecabank_client = new Cecabank\Client($config);

    try {
        $cecabank_client->checkTransaction($_POST);
    } catch (\Exception $e) {
        $message = 'Ha ocurrido un error con el pago: '.$e->getMessage();
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode($message), 'NONSSL', true, false));
    }

    $payment_module = new cecabank();

    require(DIR_WS_CLASSES . "order.php");
    $order = new order($_POST['Num_operacion']);

$order_status_query = tep_db_query("SELECT orders_status_id FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_name = '" . $order->info['orders_status'] . "' AND language_id = '" . $languages_id . "'");
    $order_status = tep_db_fetch_array($order_status_query);
    $order->info['order_status'] = $order_status['orders_status_id'];
    require(DIR_WS_CLASSES . "order_total.php");
    $order_total_modules = new order_total();
    //Set some globals (expected by osCommerce)
    $customer_id = $order->customer['id'];
    $order_totals = $order->totals;
    //Update order status
    $payment_module->update_order($_POST['Num_operacion']);

    echo $cecabank_client->successCode();
?>
