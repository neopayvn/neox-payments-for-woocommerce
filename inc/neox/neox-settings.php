<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for NeoX Gateway.
 * @since 1.0.0
 */
return array(
	'enabled'       => array(
		'title'   => __( 'Enable/Disable', 'neox-payments-for-woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'NeoX Payment Gateway (by NeoPay)', 'neox-payments-for-woocommerce' ),
		'default' => 'no'
	),
	'testmode'      => array(
		'title'       => __( 'NeoX Sandbox', 'neox-payments-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable NeoX sandbox (testing)', 'neox-payments-for-woocommerce' ),
		'default'     => 'no',
		'description' => sprintf( __( 'NeoX sandbox can be used to test payments. See <a href="%s">the testing info</a>.', 'neox-payments-for-woocommerce' ), 'https://developer.neopay.vn/summary/homepage/' ),
	),
	'title'         => array(
		'title'       => __( 'Title', 'neox-payments-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'neox-payments-for-woocommerce' ),
		'default'     => __( 'NeoX payment gateway', 'neox-payments-for-woocommerce' ),
		'desc_tip'    => true,
	),
	'description'   => array(
		'title'       => __( 'Description', 'neox-payments-for-woocommerce' ),
		'type'        => 'textarea',
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'neox-payments-for-woocommerce' ),
		'default'     => __( 'Payment by domestic ATM card, Visa, MasterCard or NeoX wallet.', 'neox-payments-for-woocommerce' )
	),
	'order_button_text'   => array(
		'title'       => __( 'Button text', 'neox-payments-for-woocommerce' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'Button label in the checkout page.', 'neox-payments-for-woocommerce' ),
		'default'     => __( 'Pay with NeoX', 'neox-payments-for-woocommerce' )
	),
    'payment_method'   => array(
        'title'       => __( 'Payment Method', 'neox-payments-for-woocommerce' ),
        'description' => __( 'If you do not select any payment method, the default will be 4 methods', 'neox-payments-for-woocommerce' ),
        'type'        => 'title',
    ),
    'wallet'       => array(
        'type'    => 'checkbox',
        'label'   => __( 'NeoX Wallet', 'neox-payments-for-woocommerce' ),
        'default' => 'yes'
    ),
    'atm'       => array(
        'type'    => 'checkbox',
        'label'   => __( 'Domestic card', 'neox-payments-for-woocommerce' ),
        'default' => 'yes'
    ),
    'cc'       => array(
        'type'    => 'checkbox',
        'label'   => __( 'International card', 'neox-payments-for-woocommerce' ),
        'default' => 'yes'
    ),
    'qr'       => array(
        'type'    => 'checkbox',
        'label'   => __( 'Bank transfer', 'neox-payments-for-woocommerce' ),
        'default' => 'yes'
    ),
	'api_details'   => array(
		'title'       => __( 'API Credentials', 'neox-payments-for-woocommerce' ),
		'type'        => 'title',
		'description' => sprintf( __( 'Enter your NeoX API credentials. Contact NeoX to have your credentials %shere%s.', 'neox-payments-for-woocommerce' ), '<a href="https://www.neox.vn/lien-he/">', '</a>' ),
	),
	'merchant_code'   => array(
		'title'       => __( 'Merchant code', 'neox-payments-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Get your Merchant code from NeoX.', 'neox-payments-for-woocommerce' ),
		'default'     => 'RZRGNY',
		'desc_tip'    => true,
		'placeholder' => __( 'Required. Provided by NeoX.', 'neox-payments-for-woocommerce' )
	),
	'secret_key' => array(
		'title'       => __( 'Secret key', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'Get your Secure Secret from NeoX.', 'neox-payments-for-woocommerce' ),
		'default'     => '4F99C21FE8A14FD198FA00D18662A63B',
		'desc_tip'    => true,
		'placeholder' => __( 'Required. Provided by NeoX.', 'neox-payments-for-woocommerce' )
	),
    'locale' => array(
        'title' => __('Locale', 'neox-payments-for-woocommerce'),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'description' => __('Choose your locale', 'neox-payments-for-woocommerce'),
        'desc_tip' => true,
        'default' => 'vn',
        'options' => array(
            'vn' => 'vn',
            'en' => 'en'
        )
    ),
    'return_url' => array(
        'title' => __('Return Page', 'neox-payments-for-woocommerce'),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'description' => __('Choose return page', 'neox-payments-for-woocommerce'),
        'desc_tip' => true,
        'default' => '',
        'options' => NeoX_Pages::get_pages()
    ),
	'more_info'     => array(
		'title'       => __( 'Instant Payment Notification (IPN)', 'neox-payments-for-woocommerce' ),
		'type'        => 'title',
		'description' =>
			sprintf( 'URL: <code>%s</code>', NeoX_Payment::get_neox_ipn_url() ) . '<p/>' .
			sprintf( __( '%sContact NeoX%s to configure this URL on its site. <strong>This is required  based on its guidelines.</strong>', 'neox-payments-for-woocommerce' ), '<a href="https://www.neox.vn/lien-he/">', '</a>' ),
	),
	'debug'         => array(
		'title'       => __( 'Debug log', 'neox-payments-for-woocommerce' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'neox-payments-for-woocommerce' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log events, such as IPN requests, inside %s', 'neox-payments-for-woocommerce' ), '<code>' . WC_Log_Handler_File::get_log_file_path('NeoX_Payment') . '</code>' ),
	),
);