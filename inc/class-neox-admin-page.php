<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create the admin page under wp-admin -> WooCommerce -> NeoX
 *
 * @author   hoant
 *
 */
class NeoX_Admin_Page
{
    /**
     * @var string The message to display after saving settings
     */
    var $message = '';

    /**
     * NeoX_Admin_Page constructor.
     */
    public function __construct()
    {
        // Catch and run the save_settings() action
        if (isset($_REQUEST['neox_nonce']) && isset ($_REQUEST['action']) && 'neox_save_settings' == $_REQUEST['action']) {
            $this->save_settings();
        }

        add_action('admin_menu', array($this, 'register_submenu_page'));
    }

    /**
     * Save settings for the plugin
     */
    public function save_settings()
    {
        if (wp_verify_nonce($_REQUEST['neox_nonce'], 'neox_save_settings')) {
            $settings = wc_clean(wp_unslash($_REQUEST['settings']));
            if (is_array($settings)) {
                $settings['change_currency_symbol']['text'] = sanitize_text_field($settings['change_currency_symbol']['text']);
                $settings['convert_price']['text'] = sanitize_text_field($settings['convert_price']['text']);
                update_option('neox', $settings);
                $this->message =
                    '<div class="updated notice"><p><strong>' .
                    __('Settings saved', 'neox-payments-for-woocommerce') .
                    '</p></strong></div>';
            }
        } else {
            $this->message =
                '<div class="error notice"><p><strong>' .
                __('Can not save settings! Please refresh this page.', 'neox-payments-for-woocommerce') .
                '</p></strong></div>';
        }
    }

    /**
     * Register the sub-menu under "WooCommerce"
     * Link: http://my-site.com/wp-admin/admin.php?page=neox
     */
    public function register_submenu_page()
    {
        add_submenu_page(
            'woocommerce',
            __('NeoX Settings', 'neox-payments-for-woocommerce'),
            'NeoX',
            'manage_options',
            'neox-payments-for-woocommerce',
            array($this, 'admin_page_html')
        );
    }

    /**
     * Generate the HTML code of the settings page
     */
    public function admin_page_html()
    {
        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        $settings = NeoX::get_settings();
        ?>
        <div class="wrap">
            <h1><?= esc_html(get_admin_page_title()); ?></h1>
            <form name="woocommerce_for_vietnam" method="post">
                <?php echo wp_kses_post($this->message) ?>
                <input type="hidden" id="action" name="action" value="neox_save_settings">
                <input type="hidden" id="neox_nonce" name="neox_nonce"
                       value="<?php echo wp_create_nonce('neox_save_settings') ?>">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row"><?php _e('Add the NeoX Payment Gateway', 'neox-payments-for-woocommerce') ?></th>
                        <td>
                            <input name="settings[add_neox_gateway][enabled]" type="hidden" value="no">
                            <input name="settings[add_neox_gateway][enabled]" type="checkbox"
                                   id="add_neox_gateway" value="yes"
                                <?php if ('yes' == $settings['add_neox_gateway']['enabled'])
                                    echo 'checked="checked"' ?>>
                            <label for="add_neox_gateway"><?php _e('Enabled', 'neox-payments-for-woocommerce') ?></label>
                            <br/>
                            <br/>
                            <label for="">
                                <?php
                                echo sprintf(__('Your store currency is <code>%s</code>. ', 'neox-payments-for-woocommerce'), get_woocommerce_currency());
                                $supported_currencies = array("HKD","CHF","SGD","CNY","AUD","CAD","JPY","GBP","EUR","USD","VND");
                                if (in_array(get_woocommerce_currency(), $supported_currencies)) { 
                                    _e('NeoX can work on your site.', 'neox-payments-for-woocommerce');
                                    echo '<br/>';
                                    if ('yes' == $settings['add_neox_gateway']['enabled']) {
                                        echo sprintf(__('Please configure this gateway under <a href="%s">WooCommerce -> Settings -> Checkout</a>.', 'neox-payments-for-woocommerce'), admin_url('admin.php?page=wc-settings&tab=checkout&section=neox_payment'));
                                    }
                                } else {
                                    _e('<span style="color: red" ">This gateway is not active on your site. Because NeoX only support HKD, CHF, SGD, CNY, AUD, CAD, JPY, GBP, EUR, USD, VND.</span>', 'neox-payments-for-woocommerce');
                                }
                                ?>
                            </label>
                        </td>
                    <tr>
                        <th scope="row"><?php _e('Change currency symbol', 'neox-payments-for-woocommerce') ?></th>
                        <td>
                            <input name="settings[change_currency_symbol][enabled]" type="hidden" value="no">
                            <input name="settings[change_currency_symbol][enabled]" type="checkbox"
                                   id="change_currency_symbol" value="yes"
                                <?php if ('yes' == $settings['change_currency_symbol']['enabled'])
                                    echo 'checked="checked"' ?>>
                            <label for="change_currency_symbol"><?php _e('Enabled', 'neox-payments-for-woocommerce') ?></label>
                            <br/>
                            <br/>
                            <input type="text" name="settings[change_currency_symbol][text]"
                                   value="<?php echo esc_attr(sanitize_text_field(wp_unslash($settings['change_currency_symbol']['text']))) ?>"
                                   id="change_currency_symbol_text" class="small-text">
                            <label for="change_currency_symbol_text"><?php _e('Insert a text to change the default symbol <code>đ</code>. E.g: <code>¥</code>, <code>£</code>, <code>€</code>', 'neox-payments-for-woocommerce') ?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Convert <code>000</code> of prices to K (or anything)', 'neox-payments-for-woocommerce') ?></th>
                        <td>
                            <input name="settings[convert_price][enabled]" type="hidden" value="no">
                            <input name="settings[convert_price][enabled]" type="checkbox" id="convert_price"
                                   value="yes"
                                <?php if ('yes' == $settings['convert_price']['enabled'])
                                    echo 'checked="checked"' ?>>
                            <label for="convert_price"><?php _e('Enabled', 'neox-payments-for-woocommerce') ?></label>

                            <fieldset><br/>
                                <input type="text" name="settings[convert_price][text]"
                                       value="<?php echo esc_attr(sanitize_text_field(wp_unslash($settings['convert_price']['text']))) ?>"
                                       id="convert_price_text" class="small-text">
                                <label for="convert_price_text"><?php _e('Choose what you want to change. E.g:', 'neox-payments-for-woocommerce') ?>
                                    <code>K</code>, <code>nghìn</code>, <code>ngàn</code>, <code>thousand</code></label>
                            </fieldset>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                </p>

            </form>
            <div id="neox-admin-footer"
                 style="border: 1px dotted; padding: 5px;">
                <?php
                printf(__('Wanna get support or give feedback? Please <a href="%1$s">rate NeoX</a> or post questions <a href="%2$s">in the forum</a>!', 'neox-payments-for-woocommerce'),
                    'https://wordpress.org/support/plugin/neox/reviews/',
                    'https://wordpress.org/support/plugin/neox/'
                )
                ?>
            </div>
        </div><!-- #wrap ->
        <?php
    }

}