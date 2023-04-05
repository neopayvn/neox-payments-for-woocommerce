<?php
/**
 * Plugin Name: NeoX Payments for WooCommerce
 * Plugin URI: https://github.com/neopayvn/neox-payments-for-woocommerce
 * Description: This plugin provide fast, secure, and reliable payment options for all types of businesses.
 * Author: neox
 * Author URI: https://profiles.wordpress.org/neopayvn
 * Text Domain: neox-payments-for-woocommerce
 * Domain Path: /languages
 * Version: 1.0.4
 *
 * WC requires at least: 3.0
 * WC tested up to: 7.0.0
 *
 * License: GPLv2+
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WOO_NEOX_DIR', plugin_dir_path(__FILE__));
define('WOO_NEOX_URL', plugins_url('/', __FILE__));

/**
 * Start the instance
 */

new NeoX();

/**
 * The main class of the plugin
 *
 * @author   neox
 */
class NeoX
{
    /**
     * @var array The default settings for the whole plugin
     */
    static $default_settings = array(
        'change_currency_symbol' =>
            array(
                'enabled' => 'yes',
                'text' => 'VND',
            ),
        'convert_price' =>
            array(
                'enabled' => 'yes',
                'text' => 'K',
            ),
        'add_neox_gateway' =>
            array(
                'enabled' => 'yes',
            ),
    );

    protected $Currency;
    protected $Admin_Page;

    /**
     *
     * Setup class.
     *
     */
    public function __construct()
    {
        add_action('init', array($this, 'init'));
    }

    /**
     * Throw a notice if WooCommerce is NOT active
     */
    public function notice_if_not_woocommerce()
    {
        $class = 'notice notice-warning';
        $message = __('NeoX is not running because WooCommerce is not active. Please activate both plugins.',
            'neo-pay');
        printf('<div class="%1$s"><p><strong>%2$s</strong></p></div>', $class, $message);
    }

    /**
     * Run this method under the "init" action
     */
    public function init()
    {
        // Load the localization feature
        $this->i18n();

        if (class_exists('WooCommerce')) {
            // Run this plugin normally if WooCommerce is active
            $this->main();
            // Add "Settings" link when the plugin is active
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
        } else {
            // Throw a notice if WooCommerce is NOT active
            add_action('admin_notices', array($this, 'notice_if_not_woocommerce'));
        }
    }

    /**
     * Localize the plugin
     * @since 1.0.0
     */
    public function i18n()
    {
        add_action('plugins_loaded', array(&$this, 'plugin_init'));
        load_plugin_textdomain('neox-payments-for-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * The main method to load the components
     */
    public function main()
    {
        if (is_admin()) {
            // Add the admin setting page
            include(WOO_NEOX_DIR . 'inc/class-neox-admin-page.php');
            $this->Admin_Page = new NeoX_Admin_Page();
        }

        $settings = self::get_settings();
        $supported_currencies = array("HKD","CHF","SGD","CNY","AUD","CAD","JPY","GBP","EUR","USD","VND");
        // Check if "Add the NeoX Gateway" is enabled
        if ('yes' == $settings['add_neox_gateway']['enabled']
            and in_array(get_woocommerce_currency(), $supported_currencies)
        ) {
            include('inc/class-neox-payment.php');
            include('inc/class-neox-pages.php');

            add_filter('woocommerce_payment_gateways', function ($methods) {
                $methods[] = 'NeoX_Payment';
                return $methods;
            });
        }

        include(WOO_NEOX_DIR . 'inc/class-neox-currency.php');
        $this->Currency = new WooNeoX_Currency();

        // Check if "Change VND currency symbol" is enabled
        if ('yes' == $settings['change_currency_symbol']['enabled']) {
            $this->Currency->change_currency_symbol($settings['change_currency_symbol']['text']);
        }

        // Check if "Convert 000 of prices to K (or anything)" is enabled
        if ('yes' == $settings['convert_price']['enabled']) {
            $this->Currency->convert_price_thousand_to_k($settings['convert_price']['text']);
        }

    }

    /**
     * The wrapper method to get the settings of the plugin
     * @return array
     */
    static function get_settings()
    {
        $settings = get_option('neox', self::$default_settings);
        $settings = wp_parse_args($settings, self::$default_settings);
        return $settings;
    }


    /**
     * Add "Settings" link in the Plugins list page when the plugin is active
     */
    public function add_settings_link($links)
    {
        $settings = array('<a href="' . admin_url('admin.php?page=neox-payments-for-woocommerce') . '">' . __('Settings', 'neox-payments-for-woocommerce') . '</a>');
        $links = array_reverse(array_merge($links, $settings));
        return $links;
    }

}
