<?php
/**
 * cecabank.php payment module texts for Cecabank (english)
 *
 * @package paymentMethod
 * @copyright Copyright 2019 Cecabank S.A.
 * @license GNU Public License V2.0
 */

// Security notice shown in the admin while the configured secret key is the
// legacy 8-character one. The %s placeholders are replaced with links.
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_TITLE', 'Action required: pending security update');
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_INTRO', 'Your merchant account is configured with an 8-character key. To complete the security adaptation you must:');
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_STEP_KEY', 'Set the new 32-character key, available in the %s, in your merchant settings.');
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_PORTAL', 'Virtual POS Administration Portal');
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_STEP_INFO', '📖 More information: %s %s');
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_BULLETIN', 'Security Bulletin');
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_MANUAL', 'Plugin manual');
  define('MODULE_PAYMENT_CECABANK_TEXT_SECRET_WARNING_FOOTER', 'If you have already completed these updates, you can ignore this message.');
?>
