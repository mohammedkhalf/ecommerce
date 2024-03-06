<?php

/**
 * Cowpay API interface for all requests.
 * Only this class knows how to call cowpay servers to make payments.
 * Singleton class
 */
class WC_Gateway_Cowpay_API_Handler
{
    private static ?WC_Gateway_Cowpay_API_Handler $instance = null;
    private $settings;

    protected static $production_host = 'apigateway.cowpay.me:8000';
    protected static $staging_host = 'staging.cowpay.me';
    protected static $identity_staging_host = 'sit.cowpay.me:8002';
    protected static $identity_production_host = 'identity.cowpay.me:8002';
    protected static $endpoint_charge_fawry = 'payment/Pay';
    protected static $endpoint_charge_cc = 'payment/Pay';
    protected static $endpoint_charge_cash_collection = 'api/v1/charge/cash-collection';
    protected static $endpoint_load_iframe_token = 'api/v1/iframe/token';
    protected static $endpoint_checkout_url = 'api/v1/iframe/load';
    protected static $endpoint_meeza_wallet = 'api/v2/charge/upg/request-to-pay';
    protected static $endpoint_meeza_card = 'api/v2/charge/upg/direct';
    protected static $endpoint_get_token = 'GetToken';

    public $notify_url;

    public static function get_instance()
    {
        if (self::$instance == null) {
            self::$instance = new WC_Gateway_Cowpay_API_Handler();
        }
        return self::$instance;
    }
    private function __construct()
    {
        $this->settings = Cowpay_Admin_Settings::getInstance();
    }

    /**
     * Make fawry charge request
     * @param array $fawry_params fawry request params to send
     * @link https://docs.cowpay.me/api/fawry
     */
    public function charge_fawry($fawry_params)
    {
        $tokenUrl = $this->make_token_url(self::$endpoint_get_token);
        $auth_token = esc_html($this->settings->get_active_token($tokenUrl));
        $url = $this->make_url(self::$endpoint_charge_fawry);
        $raw_response = wp_remote_post($url, array(   //wp_safe_remote_post
            'body' => json_encode($fawry_params),
            'httpversion' => "1.1",
            // 'timeout' => 15.0, // default is 5.0 seconds
            'headers' => array(
                "Accept" => "application/json",
                "Authorization" => "Bearer $auth_token",
                "cache-control" => "no-cache",
                "content-type" => "application/json",
            ),
        ));

        if (is_wp_error($raw_response)) {
            return $raw_response;
        } elseif (empty($raw_response['body'])) {
            return new WP_Error('cowpay_api_empty_response', __('Server Error, empty response'));
        }
        $objResponse = json_decode($raw_response['body']);
        return $objResponse;
    }

    /**
     * Make card charge request
     * @param array $cc_params credit card request params to send
     * @link https://docs.cowpay.me/api/card
    */
    public function charge_cc($cc_params)
    {
        $tokenUrl = $this->make_token_url(self::$endpoint_get_token);
        $auth_token = esc_html($this->settings->get_active_token($tokenUrl));
        $url = $this->make_url(self::$endpoint_charge_cc);
        $raw_response = wp_remote_post($url, array(   //wp_safe_remote_post
            'body' => json_encode($cc_params),
            'httpversion' => "1.1",
            'timeout' => 15.0, // default is 5.0 seconds
            'headers' => array(
                "Accept" => "application/json",
                "Authorization" => "Bearer $auth_token",
                "cache-control" => "no-cache",
                "content-type" => "application/json",
            ),
        ));

        if (is_wp_error($raw_response)) {
            return $raw_response;
        } elseif (empty($raw_response['body'])) {
            return new WP_Error('cowpay_api_empty_response', __('Server Error, empty response'));
        }

        $objResponse = json_decode($raw_response['body']);
        return $objResponse;
    }

     /**
     * Make wallet charge request
     * @param array $fawry_params fawry request params to send
     * @link https://nextdocs.cowpay.me/payments-api/upg
     */
    public function charge_meeza_wallet($meeza_params)
    {
        $url = $this->make_url(self::$endpoint_meeza_wallet);
        $auth_token = esc_html($this->settings->get_active_token());
        $raw_response = wp_safe_remote_post($url, array(
            'body' => json_encode($meeza_params),
            'httpversion' => "1.1",
            // 'timeout' => 15.0, // default is 5.0 seconds
            'headers' => array(
                "Accept" => "*/*",
                "Authorization" => "Bearer $auth_token",
                "cache-control" => "no-cache",
                "content-type" => "application/json",
            ),
        ));
        if (is_wp_error($raw_response)) {
            return $raw_response;
        } elseif (empty($raw_response['body'])) {
            return new WP_Error('cowpay_api_empty_response', __('Server Error, empty response'));
        }
        $objResponse = json_decode($raw_response['body']);
        if ($objResponse->status_code == 200) return $objResponse;
        // 400+ status code
        //? should we return WP_Error;
        return $objResponse; // return response with errors key for now;
    }

    /**
     * Make Meeza Card charge request
     * @param array $meeza_card_params Meeza request params to send
     * @link https://nextdocs.cowpay.me/payments-api/upgcard
    */
    public function charge_meeza_card($meeza_card_params)
    {
        $url = $this->make_url(self::$endpoint_meeza_card);
        $auth_token = esc_html($this->settings->get_active_token());
        $raw_response = wp_safe_remote_post($url, array(
            'body' => json_encode($meeza_card_params),
            'httpversion' => "1.1",
            // 'timeout' => 15.0, // default is 5.0 seconds
            'headers' => array(
                "Accept" => "*/*",
                "Authorization" => "Bearer $auth_token",
                "cache-control" => "no-cache",
                "content-type" => "application/json",
            ),
        ));
        if (is_wp_error($raw_response)) {
            return $raw_response;
        } elseif (empty($raw_response['body'])) {
            return new WP_Error('cowpay_api_empty_response', __('Server Error, empty response'));
        }
        $objResponse = json_decode($raw_response['body']);
        if ($objResponse->status_code == 200) return $objResponse;
        // 400+ status code
        //? should we return WP_Error;
        return $objResponse; // return response with errors key for now;
    }



    /**
     * Make card charge request
     * @param array $cc_params credit card request params to send
     * @link https://docs.cowpay.me/api/card
     */
    public function charge_cash_collection($cash_collection_params)
    {
        $url = $this->make_url(self::$endpoint_charge_cash_collection);
        $auth_token = esc_html($this->settings->get_active_token());
        $raw_response = wp_safe_remote_post($url, array(
            'body' => json_encode($cash_collection_params),
            'httpversion' => "2.00",
            'headers' => array(
                "Accept" => "application/json",
                "Authorization" => "Bearer $auth_token",
                "cache-control" => "no-cache",
                "content-type" => "application/json",
            ),
        ));
        if (is_wp_error($raw_response)) {
            return $raw_response;
        } elseif (empty($raw_response['body'])) {
            return new WP_Error('cowpay_api_empty_response', __('Server Error, empty response','woo-cowpay'));
        }
        $objResponse = json_decode($raw_response['body']);
        if ($objResponse->status_code == 200) return $objResponse;
        // 400+ status code
        //? should we return WP_Error;
        return $objResponse; // return response with errors key for now;
    }

    /**
     * Make card charge request
     * @param array $token_params token request params to send
     * @link https://docs.cowpay.me/api/iframe
     */
    public function load_iframe_token($token_params)
    {
        $url = $this->make_url(self::$endpoint_load_iframe_token);
        $auth_token = esc_html($this->settings->get_active_token());
        $raw_response = wp_safe_remote_post($url, array(
            'body' => json_encode($token_params),
            'httpversion' => "1.1",
            'headers' => array(
                "Accept" => "*/*",
                "Authorization" => "Bearer $auth_token",
                "cache-control" => "no-cache",
                "content-type" => "application/json",
            ),
        ));
        if (is_wp_error($raw_response)) {
            return $raw_response;
        } elseif (empty($raw_response['body'])) {
            return new WP_Error('cowpay_api_empty_response', __('Server Error, empty response','woo-cowpay'));
        }
        $objResponse = json_decode($raw_response['body']);
        if ($objResponse->status_code == 200) return $objResponse;
        // 400+ status code
        //? should we return WP_Error;
        return $objResponse; // return response with errors key for now;
    }

    /**
     * Returns checkout url of cowpay servers
     * @param string $token token generated from load_iframe_token.
     * @param string $referer_url when checkout process is done, cowpay redirect user to this url
     */
    public function get_checkout_url($token, $referer_url)
    {
        return $this->make_url(self::$endpoint_checkout_url . "/$token?referer=$referer_url");
    }

    /**
     * Returns cowpay host depending on selected environment in admin settings
     */
    public function get_active_host()
    {
        return $this->settings->get_environment() == 1 ? self::$production_host : self::$staging_host;
    }

    public function get_token_active_host()
    {
        return $this->settings->get_environment() == 1 ? self::$identity_production_host : self::$identity_staging_host;
    }

    /**
     * return url from endpoint path, take into
     * account http/https and production/staging selection
     */
    protected function make_url($path)
    {
        $host = $this->get_active_host();
        $schema = is_ssl() ? "https" : "http";
        $url = "$schema://$host/$path";
        return esc_url_raw($url);
    }

    public function make_token_url($path)
    {
        $host = $this->get_token_active_host();
        $schema = is_ssl() ? "https" : "http";
        $url = "$schema://$host/$path";
        return esc_url_raw($url);
    }
}
