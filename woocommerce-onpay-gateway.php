<?php
/*
Plugin Name: WooCommerce Onpay Payment Gateway
Description: Onpay Payment gateway for woocommerce
Version: 1.0.0
Author: animir
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function init_onpay_gateway_class() {
    /**
     * Onpay Payment Gateway
     *
     * Provides a PayPal Standard Payment Gateway.
     *
     * @class 		WC_Gateway_Onpay
     * @extends		WC_Payment_Gateway
     * @version		0.0.1
     * @package		WooCommerce/Classes/Payment
     * @author 		animir
     */
    class WC_Gateway_Onpay extends WC_Payment_Gateway
    {
        /** @var boolean Whether or not logging is enabled */
        public static $log_enabled = false;

        /** @var WC_Logger Logger instance */
        public static $log = false;

        /**
         * Constructor for the gateway.
         */
        public function __construct() {
            $this->id                 = 'onpay';
            $this->has_fields         = false;
            $this->order_button_text  = __( 'Pay', 'woocommerce' );
            $this->method_title       = __( 'Onpay', 'woocommerce' );
            $this->method_description = __( 'Оплата через платежный интегратор Onpay.ru', 'woocommerce' );
            $this->supports           = array(
                'products'
            );

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->onpay_login = $this->get_option('onpay_login');
            $this->onpay_key = $this->get_option('onpay_key');
            $this->onpay_price_final = $this->get_option('onpay_price_final', 'yes');
            $this->onpay_form_id = $this->get_option('onpay_form_id');

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_api_onpay', array( $this, 'onpay_callback' ) );
        }

        public function get_icon() {
            return apply_filters( 'woocommerce_gateway_icon',
                "<img src='" . plugins_url( 'images/payment_systems.jpg', __FILE__) . "' alt='" . esc_attr( $this->get_title() ) . "' />",
                $this->id );
        }

        /**
         * Initialise Gateway Settings
         *
         * Store all settings in a single database entry
         * and make sure the $settings array is either the default
         * or the settings stored in the database.
         *
         * @uses get_option(), add_option()
         */
        public function init_settings() {

            // Load form_field settings
            $this->settings = get_option( $this->plugin_id . $this->id . '_settings', null );

            if ( ! $this->settings || ! is_array( $this->settings ) ) {

                $this->settings = array();

                // If there are no settings defined, load defaults
                if ( $form_fields = $this->get_form_fields() ) {

                    foreach ( $form_fields as $k => $v ) {
                        $this->settings[ $k ] = isset( $v['default'] ) ? $v['default'] : '';
                    }
                }
            }

            if ( ! empty( $this->settings ) && is_array( $this->settings ) ) {
                $this->settings = array_map( array( $this, 'format_settings' ), $this->settings );
                $this->enabled  = isset( $this->settings['enabled'] ) && $this->settings['enabled'] == 'yes' ? 'yes' : 'no';
            }
        }

        /**
         * Initialise Gateway Settings Form Fields
         */
        public function init_form_fields() {
            $this->form_fields = include('includes/settings-onpay.php');
        }

        /**
         * Get the form fields after they are initialized
         * @return array of options
         */
        public function get_form_fields() {
            return apply_filters( 'woocommerce_settings_api_form_fields_' . $this->id, $this->form_fields );
        }

        public function process_payment( $order_id ) {
            $order          = wc_get_order( $order_id );
            $pay_mode = 'fix';
            $onpay_key = $this->onpay_key;
            $onpay_md5 = md5($pay_mode.";$order->order_total;" . "RUR" . ";" . $order->get_order_number() . ";yes;$onpay_key");
            $pay_url = "http://secure.onpay.ru/pay/".$this->onpay_login
                ."?price_final=".$this->onpay_price_final
                ."&ln=ru"
                ."&f=".$this->onpay_form_id
                ."&pay_mode=$pay_mode"
                ."&pay_for=".$order->id
                ."&price=".$order->order_total
                ."&ticker=RUR"
                ."&convert=yes"
                ."&md5=".$onpay_md5
                ."&user_email=".urlencode($order->billing_email)
                ."&url_success_enc=".esc_url( add_query_arg( 'utm_nooverride', '1', $this->get_return_url( $order ) ) )
                ."&url_fail_enc=".esc_url( $order->get_cancel_order_url() );
            return array(
                'result'   => 'success',
                'redirect' => $pay_url
            );
        }

        private function onpay_to_float($sum) {
            if (strpos($sum, ".")) {
                $sum = round($sum, 2);
            } else {
                $sum = $sum . ".0";
            }
            return $sum;
        }

        private function onpay_check($request) {
            $check = array(
                'type' => 'check',
                'pay_for' => $request['pay_for'],
                'amount' => $this->onpay_to_float($request['amount']),
                'currency' => trim($request['way']),
                'mode' => trim($request['mode']),
                'key' => $this->onpay_key,
            );
            $check['signature_string'] = implode(";", $check);
            $check['signature'] = sha1($check['signature_string']);
            $checkOut = array(
                'type' => 'check',
                'status' => 'false',
                'pay_for' => $request['pay_for'],
                'key' => $this->onpay_key
            );
            $amount = floatval($request['amount']);
            if($this->onpay_validate($request, $check['signature'])) {
                $order = wc_get_order( $request['pay_for'] );
                if (floatval($order->order_total) === $amount) {
                    $checkOut['status'] = 'true';
                }
            }
            $this->onpay_response($checkOut, $request);
        }

        private function onpay_pay($request) {
            $pay = array(
                'type' => 'pay',
                'pay_for' => $request['pay_for'],
                'payment.amount' => $this->onpay_to_float($request['payment']['amount']),
                'payment.currency' => trim($request['payment']['way']),
                'amount' => $this->onpay_to_float($request['balance']['amount']),
                'currency' => trim($request['balance']['way']),
                'key' => $this->onpay_key,
            );
            $pay['signature_string'] = implode(";", $pay);
            $pay['signature'] = sha1($pay['signature_string']);
            $payOut = array(
                'type' => 'pay',
                'status' => 'false',
                'pay_for' => $request['pay_for'],
                'key' => $this->onpay_key,
            );
            $amount = floatval($request['balance']['amount']);
            if($this->onpay_validate($request, $pay['signature'])) {
                $order = wc_get_order( $request['pay_for'] );
                if (floatval($order->order_total === $amount)) {
                    $order->payment_complete();
                    $order->add_order_note('Paid via Onpay');
                    $payOut['status'] = 'true';
                }
            }
            $this->onpay_response($payOut, $request);
        }

        private function onpay_response($response, $request) {
            $response['signature_string'] = implode(";", $response);
            $response['signature'] = sha1($response['signature_string']);
            $out = "{\"status\":{$response['status']},\"pay_for\":\"{$response['pay_for']}\",\"signature\":\"{$response['signature']}\"}";

            header("Content-type: application/json; charset=utf-8");
            echo iconv("cp1251", "utf-8", $out);
            die;
        }

        private function onpay_validate($request, $signature) {
            if($request['signature'] != $signature) {
                return false;
            }
            return true;
        }

        private function onpay_get_request()
        {
            $ret = false;
            if (function_exists('json_decode')) {
                if ($resource = fopen('php://input', 'r')){
                    $input = "";
                    while (!feof($resource)) {
                        $input .= fread($resource, 1024);
                    }
                    fclose($resource);
                    $input = trim($input);

                    $ret = json_decode($input, true);
                    if (is_null($ret)) {
                        $ret = json_decode(iconv("cp1251", "utf-8", $input), true);
                    }
                }
            }
            return $ret;
        }

        public function onpay_callback()
        {
            $request = $this->onpay_get_request();
            $errorResponse = array(
                'status' => 'false',
                'pay_for' => isset($request['pay_for']) ? $request['pay_for'] : '',
                'key' => $this->onpay_key
            );
            if (isset($request['type'])) {
                if ($request['type'] == 'check') {
                    $this->onpay_check($request);
                } elseif ($request['type'] == 'pay') {
                    $this->onpay_pay($request);
                } else {
                    $this->onpay_response($errorResponse, $request);
                }
            } else {
                $this->onpay_response($errorResponse, $request);
            }
        }
    }

    function add_onpay_gateway_class( $methods ) {
        $methods[] = 'WC_Gateway_Onpay';
        return $methods;
    }

    add_filter( 'woocommerce_payment_gateways', 'add_onpay_gateway_class' );
}

add_action( 'plugins_loaded', 'init_onpay_gateway_class', 0);