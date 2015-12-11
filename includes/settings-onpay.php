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
        'default'     => __( 'Оплатить картой или электронной валютой', 'woocommerce' ),
        'desc_tip'    => true,
    ),
    'description' => array(
        'title'       => __( 'Description', 'woocommerce' ),
        'type'        => 'text',
        'desc_tip'    => true,
        'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
        'default'     => __( 'Оплата картами или электронной валютой', 'woocommerce' )
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
            'yes' => __('yes', 'woocommerce'),
            'no'  => __('no', 'woocommerce')
        )
    ),
    'onpay_form_id' => array(
        'title'       => __( 'Payment form design number', 'woocommerce' ),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'default'     => 7,
        'options' => array(
            7  => 7,
            8  => 8,
            10 => 10,
            11 => 11
        )
    )
);
