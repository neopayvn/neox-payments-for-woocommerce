<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract class to handle the NeoX gateways
 *
 * @author   hoant
 *
 */
abstract class WC_NeoX_Abstract extends WC_Payment_Gateway
{
    /** @var bool Whether or not logging is enabled */
    public static $log_enabled = false;

    /** @var WC_Logger Logger instance */
    public static $log = false;

    /**
     * Configure $method_title and $method_description
     */
    abstract public function configure_payment();

    /**
     * @param bool $testmode
     */
    abstract public function get_neox_payment_link($testmode);

    /**
     * Get the response description based on the response code
     * This is code is from NeoX
     *
     * @param string $responseCode
     *
     * @return string
     */
    abstract public function NeoX_getResponseDescription($responseCode);

    /**
     * Constructor for the gateway.
     */
    public function __construct()
    {
        $this->id = strtolower(get_called_class());
        $this->has_fields = false;
        $this->configure_payment();
        $this->supports = array('products');

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables.
        $this->title = sanitize_text_field($this->get_option('title'));
        $this->description = sanitize_text_field($this->get_option('description'))
            . sprintf('<br/><div align="center" class="neox-logo" id="%1$s"><img src="%2$s"></div>',
                $this->id . '_logo.png',
                apply_filters($this->id . '_logo', WOO_NEOX_URL . "assets/$this->id.png"));
        $this->order_button_text = $this->get_option('order_button_text');
        $this->testmode = 'yes' === $this->get_option('testmode', 'no');
        $this->merchant_code = sanitize_text_field($this->get_option('merchant_code'));
        $this->secret_key = sanitize_text_field($this->get_option('secret_key'));
        $this->return_url = $this->get_option('return_url');
        $this->debug = 'yes' === $this->get_option('debug', 'no');
        $this->wallet = 'yes' === $this->get_option('wallet', 'no');
        $this->atm = 'yes' === $this->get_option('atm', 'no');
        $this->cc = 'yes' === $this->get_option('cc', 'no');
        $this->qr = 'yes' === $this->get_option('qr', 'no');

        self::$log_enabled = $this->debug;
        // Process the admin options
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
            $this,
            'process_admin_options'
        ));
        add_action('woocommerce_api_' . $this->id, array($this, 'handle_neox_ipn'));
        $this->handle_neox_return_url();
    }

    /**
     * Get the IPN URL for NeoX
     */
    static function get_neox_ipn_url()
    {
        return WC()->api_request_url(get_called_class());
    }

    /**
     * Process the payment
     *
     * @param int $order_id
     *
     * @return array
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        return array(
            'result' => 'success',
            'redirect' => $this->get_pay_url($order)
        );
    }

    /**
     * Get the NeoX pay URL for an order
     * AND set the queryDR cron for this transaction
     *
     * @param WC_Order $order
     *
     * @return string
     *
     */
    public function get_pay_url($order)
    {
        self::log('get_pay_url');
        $hash_fields = array(
            "neo_MerchantCode",
            "neo_PaymentMethod",
            "neo_Currency",
            "neo_Locale",
            "neo_Version",
            "neo_Command",
            "neo_Amount",
            "neo_MerchantTxnID",
            "neo_OrderID",
            "neo_OrderInfo",
            "neo_Title",
            "neo_ReturnURL",
        );

        $methods = $this->get_methods();
        $args = array(
            'neo_Amount' => $order->get_total(),
            'neo_Command' => 'PAY',
            'neo_Currency' => get_woocommerce_currency(),
            'neo_Locale' => ('vi' == get_locale()) ? 'vi' : 'en',
            'neo_MerchantCode' => $this->merchant_code,
            'neo_MerchantTxnID' => sprintf('%1$s_%2$s', $order->get_id(), date('YmdHis')),
            'neo_OrderID' => $order->get_id(),
            'neo_OrderInfo' => $order->get_id(),
            'neo_PaymentMethod' => $methods,
            'neo_ReturnURL' => get_permalink($this->return_url),
            'neo_Title' => 'Payment',
            'neo_Version' => 1,
        );

        // Get the secure hash
        $neo_SecureHash = $this->create_neo_SecureHash($hash_fields, $args);

        // Add the secure hash to the args
        $args['neo_SecureHash'] = $neo_SecureHash;
        $http_args = http_build_query($args, '', '&');

        // Log data
        $message_log = sprintf('get_pay_url - Order ID: %1$s - http_args: %2$s', $order->get_id(), print_r($args, true));
        self::log($message_log);
        return $this->get_neox_payment_link($this->testmode) . '?' . $http_args;

    }

    /**
     * @return string
     */
    public function get_methods()
    {
        $methods = "";
        if ($this->wallet) {
            $methods .= "WALLET,";
        }
        if ($this->atm) {
            $methods .= "ATM,";
        }
        if ($this->cc) {
            $methods .= "CC,";
        }
        if ($this->qr) {
            $methods .= "QR,";
        }
        //Remove the last character ","
        return rtrim($methods, ",");
    }

    /**
     * Create the neo_SecureHash value.
     *
     * @param array $args
     *
     * @return string
     */
    public function create_neo_SecureHash($hash_fields, $args)
    {
        sort($hash_fields);
        $hash_data_str = '';
        foreach ($hash_fields as $key => $value) {
            if (isset($args[$value])) {
                $hash_data_str .= sanitize_text_field($args[$value]);
            }
        }
        $hash_data_str .= sanitize_text_field($this->secret_key);
        $hash_result = hash('sha256', $hash_data_str);
        return strtoupper($hash_result);
    }

    /**
     * Handle the return URL - GET request from NeoX
     */
    public function handle_neox_return_url()
    {
        if (isset($_GET['neo_SecureHash'])) {
            $this->process_neox_response_data(wc_clean(wp_unslash($_GET)), 'return');
        }
    }

    /**
     * Handle the repsonse data from NeoX
     *
     * @param string $args the response data from NeoX
     * @param string $type
     */
    public function process_neox_response_data($args, $type)
    {
        $types_accepted = array(
            'return',
            'ipn',
            'querydr',
        );
        // Do nothing if the type is wrong
        if (!in_array($type, $types_accepted)) {
            return;
        }

        $is_secure = false;
        // Verify hash for 'return' and 'ipn'
        switch ($type) {
            case 'return':
            case 'ipn':
                $neo_SecureHash = sanitize_text_field($args['neo_SecureHash']);

                // Remove the parameter "neo_SecureHash" for validating SecureHash
                unset($args['neo_SecureHash']);

                $is_secure = $this->check_neo_SecureHash($args, $neo_SecureHash);
                break;
        }
        // Process the data
        if ($is_secure) {
            /**
             * $neo_MerchantTxnID looks like this "139_20170418101843" or {order_id}_{date_time}
             * @see $this->get_pay_url();
             */
            $neo_MerchantTxnID = sanitize_text_field($args['neo_MerchantTxnID']);
            $neo_ResponseCode = sanitize_text_field($args['neo_ResponseCode']);

            // Get the order_id part only
            $order_id = sanitize_text_field($args['neo_OrderID']);

            $order = wc_get_order($order_id);

            // Add the order note for the reference
            $order_note = get_called_class() . sprintf(
                    __(' Gateway Info | Code: %1$s | Message: %2$s | MerchantTxnRef: %3$s | Type: %4$s', 'neox'),
                    $neo_ResponseCode,
                    $this->NeoX_getResponseDescription($neo_ResponseCode),
                    $neo_MerchantTxnID,
                    $type
                );
            $order->add_order_note($order_note);

            // Log data
            $message_log = sprintf('process_neox_response_data - Order ID: %1$s - Order Note: %2$s - http_args: %3$s', $order_id, $order_note, print_r($args, true));
            self::log($message_log);

            // Do action for the order based on the response code from NeoX
            // This is an intentional DRY switch - refer to #neo_ResponseCode below
            switch ($neo_ResponseCode) {
                case '0':
                    // If the payment is successful, update the order
                    $order->payment_complete();
                    WC()->cart->empty_cart();
                    break;
                default:
                    // For other cases, do nothing. By default, the order status is still "Pending Payment"
                    break;
            }

            // Do the last actions based on $type
            switch ($type) {
                case 'return': // Add info from NeoX and redirect to the appropriate URLs
                    wc_add_notice(__('NeoX info: ', 'neox-payments-for-woocommerce') . $this->NeoX_getResponseDescription($neo_ResponseCode), 'notice');
                    // This is an intentional DRY switch - refer to #neo_ResponseCode above
                    // I need to make sure that `ipn` case below and message_log can be executed as well.
                    switch ($neo_ResponseCode) {
                        case '0':
                            // If the payment is successful, redirect to the order page
                            wp_redirect(get_permalink($order->get_cancel_order_url_raw()));
                            break;
                        case '18':
                            // If the user cancels payment, redirect to the canceled cart page
                            wp_redirect(get_permalink($order->get_cancel_order_url_raw()));
                            break;
                        default:
                            // For other cases, redirect to the payment page
                            wp_redirect(get_permalink($order->get_checkout_payment_url()));
                            break;
                    }
                    break;
                case 'ipn': // Output the data to the page content
                    exit('responsecode=1&desc=confirm-success');
                    break;
                case 'querydr':
                    // Do nothing
                    break;
            }
        } else {
            if ('ipn' == $type) {
                exit('responsecode=0&desc=confirm-success');
            }

        }
    }

    /**
     * Whether or not the arguments and a provided $vpc_SecureHash are the same
     *
     * @param $args
     * @param $neo_SecureHash
     *
     * @return bool
     */
    public function check_neo_SecureHash($args, $neo_SecureHash)
    {
        // Generate the "neo_SecureHash" value from $args
        $hash_fields = array(
            "neo_MerchantCode",
            "neo_Currency",
            "neo_Locale",
            "neo_Version",
            "neo_Command",
            "neo_Amount",
            "neo_MerchantTxnID",
            "neo_OrderID",
            "neo_OrderInfo",
            "neo_TransactionID",
            "neo_ResponseCode",
            "neo_ResponseMsg",
            "neo_ResponseData"
        );
        $neo_SecureHash_from_args = $this->create_neo_SecureHash($hash_fields, $args);
        if ($neo_SecureHash_from_args == $neo_SecureHash) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Handle the IPN POST request from NeoX
     */
    public function handle_neox_ipn()
    {
        if (isset($_REQUEST['neo_SecureHash'])) {
            $this->process_neox_response_data(wc_clean(wp_unslash($_REQUEST)), 'ipn');
        }
    }

    /**
     * Logging method. - Copied from the WC_Gateway_Paypal Class
     *
     * @param string $message Log message.
     * @param string $level Optional. Default 'info'.
     *     emergency|alert|critical|error|warning|notice|info|debug
     * @since 1.0.0
     *
     */
    public static function log($message, $level = 'info')
    {
        if (self::$log_enabled) {
            if (empty(self::$log)) {
                self::$log = wc_get_logger();
            }
            self::$log->log($level, $message, array('source' => get_called_class()));
        }
    }

}
