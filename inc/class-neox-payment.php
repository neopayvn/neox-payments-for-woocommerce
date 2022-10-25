<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @author   neox
 */

require_once('neox/abstract-payment.php');

class NeoX_Payment extends WC_NeoX_Abstract
{
    public function configure_payment()
    {
        $this->method_title = __('NeoX Payment Gateway (by NeoPay)', 'neox-payments-for-woocommerce');
        $this->method_description = __('NeoX supports all major bank ATMs in Vietnam.', 'neox-payments-for-woocommerce');
    }

    public function get_neox_payment_link($testmode)
    {
        return $testmode ? 'https://sandbox-api.neopay.vn/pg/api/v1/paygate/neopay' : 'https://api.neopay.vn/pg/api/v1/paygate/neopay';
    }

    public function NeoX_getResponseDescription($responseCode)
    {
        switch ($responseCode) {
            case "0" :
                $result = __('Payment success', 'neox-payments-for-woocommerce');
                break;
            case "9" :
                $result = __('Orders are pending payment', 'neox-payments-for-woocommerce');
                break;
            case "18" :
                $result = __('Order cancelled by customer', 'neox-payments-for-woocommerce');
                break;
            case "19" :
                $result = __('Duplicated neo_MerchantTxnId', 'neox-payments-for-woocommerce');
                break;
            case "31" :
                $result = __('Invalid neo_OrderId', 'neox-payments-for-woocommerce');
                break;
            case "32" :
                $result = __('Invalid neo_MerchantTxnId', 'neox-payments-for-woocommerce');
                break;
            case "99" :
                $result = __('Order processing, have at least a payment in progress', 'neox-payments-for-woocommerce');
                break;
            case "-1" :
                $result = __('Order has expired', 'neox-payments-for-woocommerce');
                break;
            default :
                $result = __('Transaction failed', 'neox-payments-for-woocommerce');
        }
        return $result;
    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields()
    {
        $this->form_fields = include('neox/neox-settings.php');
    }
}