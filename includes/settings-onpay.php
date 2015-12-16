<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Onpay Gateway
 */
return array(
    'enabled' => array(
        'title'   => __( 'Enable/Disable', 'woocommerce' ),
        'type'    => 'checkbox',
        'label'   => __( 'Enable Onpay', 'woocommerce' ),
        'default' => 'yes'
    ),
    'title' => array(
        'title'       => __( 'Title', 'woocommerce' ),
        'type'        => 'text',
        'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
        'default'     => __( 'Оплатить картой, через терминал или электронной валютой', 'woocommerce' ),
        'desc_tip'    => true,
    ),
    'description' => array(
        'title'       => __( 'Description', 'woocommerce' ),
        'type'        => 'text',
        'desc_tip'    => true,
        'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
        'default'     => __( 'Внимание. Может взиматься дополнительная комиссия.', 'woocommerce' )
    ),
    'onpay_login' => array(
        'title'       => __( 'Onpay API login', 'woocommerce' ),
        'type'        => 'text',
        'description' => __( '', 'woocommerce' ),
        'desc_tip'    => true
    ),
    'onpay_key' => array(
        'title'       => __( 'Onpay API secret key', 'woocommerce' ),
        'type'        => 'text',
        'description' => __( '', 'woocommerce' ),
        'desc_tip'    => true
    ),
    'onpay_price_final' => array(
        'title'       => __( 'Store owner pays all gateway commissions', 'woocommerce' ),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'default'     => 'yes',
        'options'     => array(
            'true' => __('yes', 'woocommerce'),
            'false'  => __('no', 'woocommerce')
        )
    ),
    'onpay_form_id' => array(
        'title'       => __( 'Payment form design number', 'woocommerce' ),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'default'     => 7,
        'options' => array(
            2 => 'Стандарт',
            7 => "Иконки",
            8 => "Иконки с описанием",
            9 => "Мобильная"
        )
    )
);
