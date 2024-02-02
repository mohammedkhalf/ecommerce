<?php

/**
 * Base class for all cowpay gateways (methods)
 */
abstract class WC_Payment_Gateway_Cowpay extends WC_Payment_Gateway
{
    public $require_ssl = false;
    public $cp_admin_settings;

    function __construct()
    {
        $this->cp_admin_settings = Cowpay_Admin_Settings::getInstance();
    }

    /**
     * Output field name HTML
     *
     * Gateways which support tokenization do not require names - we don't want the data to post to the server.
     *
     * @param  string $name Field name.
     * @return string
     */
    public function field_name($name)
    {
        return $this->supports('tokenization') ? '' : ' name="' . esc_attr($this->id . '-' . $name) . '" ';
    }

    /**
     * This function should be called from the concrete class after all fields initialization.
     */
    public function init() //TODO: rename this function
    {
        // This basically defines your settings which are then loaded with init_settings()
        $this->init_form_fields();

        // After init_settings() is called, you can get the settings and load them into variables, e.g:
        // $this->title = $this->get_option( 'title' );
        $this->init_settings();

        // Turn these settings into variables we can use
        foreach ($this->settings as $setting_key => $value) {
            $this->$setting_key = $value;
        }

        // Save settings
        $this->save_admin_settings();

        // Lets check for SSL
        if ($this->require_ssl) add_action('admin_notices', array($this, 'do_ssl_check'));
    }

    public function handle_order_fail($response, $order)
    {
        // Transaction was not successful
        // Add notice to the cart
        wc_add_notice($response->operationMessage, 'error');
        // Add note to the order for your reference
        $order->add_order_note('Error:  Failure');
        //$customer_order->update_status("wc-cancelled",esc_html__('The order was failed','cowpay'));
        //echo "<pre>"; print_r($result);
    }

    /**
     * Calculate and return the merchant reference id for cowpay(cp)
     * from the given order.
     * Note: this function generates random uuids. two consecutive calls will generate
     * different reference id.
     */
    public function get_cp_merchant_reference_id(WC_Order $order)
    {
        return $order->get_order_number() . '-' . wp_generate_uuid4();
    }

    /**
     * Calculate and return the customer profile id for cowpay(cp)
     * from the given order
     */
    public function get_cp_customer_profile_id(WC_Order $order)
    {
        $billing_name = $order->get_billing_first_name();
        $guest_id = str_replace(" ", "-", "GUEST-$billing_name");
        $res = $order->get_user() ? $order->get_customer_id() : $guest_id;
        return strval($res); // id should be str
    }


    /**
     * Store cowpay related needed values to the customer order as meta. information granted from request and response.
     * These information is used to retrieve the order in the server notification.
     * @param WC_Order $order current processed order 
     * @param array $req_params req params, should contains merchant_reference_id
     * @param Object? $response object if exists
     * @return void
     */
    public function set_cowpay_meta($order, $req_params, $response = null)
    {
        //* if meta key starts with underscore '_' character, it will be private
        //* and will not be shown in the order information in the dashboard.

        $setOrderMeta = function ($k, $v) use ($order) {
            update_post_meta($order->get_id(), $k, $v);
            // don't use this right now, as it doesn't update in the database.
            //// $order->add_meta_data($k, $v);
        };
        $setOrderMeta("cp_merchantReferenceId", $req_params['merchantReferenceId']);
        $setOrderMeta("cp_customerMerchantProfileId", $req_params['customerMerchantProfileId']);

        if ($response == null) return;
        // response meta
        $setOrderMeta("cp_cowpay_reference_id",$response->data->cowpayReferenceId);
        $is_3ds = true;
        $setOrderMeta("cp_is_3ds", $is_3ds);
        if (isset($response->data->paymentGatewayReferenceId)) {
            $setOrderMeta("cp_payment_gateway_reference_id", $response->data->paymentGatewayReferenceId);
        }elseif (is_array($response) && @isset($response['paymentGatewayReferenceId'])) {
            $setOrderMeta("cp_payment_gateway_reference_id", $response['paymentGatewayReferenceId']);
        }
        

        // TODO: do_action('cowpay_meta_after_update')
    }

    /**
     * parse an error and returns readable helpful user message
     * @param WP_Error|Object $maybe_error object that may contains error
     * @return string[] user error messages
     */
    public function get_user_error_messages($maybe_error)
    {
        $return_messages = array();
        if (is_wp_error($maybe_error)) { // extract readable error messages from wp_error object
            $error_codes = $maybe_error->get_error_codes();
            foreach ($error_codes as $error_code) {
                if ($error_code == 'http_request_failed') {
                    // get more readable error instead of 'not valid url'
                    $return_messages[] = __("Sorry, we can't establish a valid connection with Cowpay servers");
                } else {
                    // get default description for other error codes
                    $return_messages[] = $maybe_error->get_error_message($error_code);
                }
            }
        } else if (
            isset($maybe_error->statusCode)
            && isset($maybe_error->operationMessage)
            && $maybe_error->statusCode != 201
        ) { // extract errors from the server response
            $errors = $maybe_error->errors;

            if (isset($errors)) {
                if (!is_array($errors))
                    $errors = get_object_vars($errors);
                $return_messages = array_values($errors);
            } else {
                // if we can't find detailed errors, return status description as the error
                $return_messages[] = $maybe_error->operationMessage;
            }
        } else if (!isset($maybe_error->statusCode)) { // server should return it in the response
            $return_messages[] = esc_html__("Unexpected Cowpay response");
        }
        return $return_messages;
    }

    /**
     * Calculate and return the description field for passed order param cowpay(cp)
     * from the given order
     */
    public function get_cp_description(WC_Order $order)
    {
        /**
         * TODO: return valid desc information for this specific order
         * see https://www.businessbloomer.com/woocommerce-easily-get-order-info-total-items-etc-from-order-object/
         * see https://www.businessbloomer.com/woocommerce-easily-get-product-info-title-sku-desc-product-object/
         */
        // array_values($order->get_items())
        $order_items = $order->get_items();
        $info = array(
            $this->cp_admin_settings->get_description(), // admin description
            $order->get_payment_method_title(),
            $order_items[array_key_first($order_items)]->get_name() // name of first item in the order
        );
        return join(', ', $info); // TODO: apply filter
    }

    /**
     * Creates the request signature from dynamic params sent and admin settings (merchant code, merchant hash)
     */
    public function get_cp_signature($amount, $merchant_reference_id, $customer_profile_id)
    {
        $message = join("", array(
            $this->cp_admin_settings->get_merchant_code(),
            $merchant_reference_id,
            $customer_profile_id,
            $amount,
            $this->cp_admin_settings->get_merchant_hash()
        ));
        
        return hash('sha256', $message);
    }

    /**
     * Save our administration woocomenrce options. Since we are not going to be doing anything special
     * we have not defined 'process_admin_options' in this class, extended class can define process_admin_options
     * to do its options logic before save.
     */
    public function save_admin_settings()
    {
        if (is_admin()) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
    }


    public function do_ssl_check()
    {
        if ($this->enabled == "yes") {
            if (get_option('woocommerce_force_ssl_checkout') == "no") {
                echo "<div class=\"error\"><p>" . sprintf(__("<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>", 'cowpay'), $this->method_title, admin_url('admin.php?page=cowpay_setting')) . "</p></div>";
            }
        }
    }

    public function get_dial_phone_number()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $data = file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip);
        $data = json_decode($data);
        $countryName = $data->geoplugin_countryName;
        $dialPhoneNumber = $this->getDailUsingCountry($countryName);
        // print_r($data->geoplugin_countryName); die;

    }

    public function getDailUsingCountry($countryName)
    {
        $countryCodes = [
            '+44' => 'UK',
            '+1' => 'USA',
            '+213' => 'Algeria',
            '+376' => 'Andorra',
            '+244' => 'Angola',
            '+1264' => 'Anguilla',
            '+1268' => 'Antigua & Barbuda',
            '+54' => 'Argentina',
            '+374' => 'Armenia',
            '+297' => 'Aruba',
            '+61' => 'Australia',
            '+43' => 'Austria',
            '+994' => 'Azerbaijan',
            '+1242' => 'Bahamas',
            '+973' => 'Bahrain',
            '+880' => 'Bangladesh',
            '+1246' => 'Barbados',
            '+375' => 'Belarus',
            '+32' => 'Belgium',
            '+501' => 'Belize',
            '+229' => 'Benin',
            '+1441' => 'Bermuda',
            '+975' => 'Bhutan',
            '+591' => 'Bolivia',
            '+387' => 'Bosnia Herzegovina',
            '+267' => 'Botswana',
            '+55' => 'Brazil',
            '+673' => 'Brunei',
            '+359' => 'Bulgaria',
            '+226' => 'Burkina Faso',
            '+257' => 'Burundi',
            '+855' => 'Cambodia',
            '+237' => 'Cameroon',
            '+1' => 'Canada',
            '+238' => 'Cape Verde Islands',
            '+1345' => 'Cayman Islands',
            '+236' => 'Central African Republic',
            '+56' => 'Chile',
            '+86' => 'China',
            '+57' => 'Colombia',
            '+269' => 'Comoros',
            '+242' => 'Congo',
            '+682' => 'Cook Islands',
            '+506' => 'Costa Rica',
            '+385' => 'Croatia',
            '+53' => 'Cuba',
            '+90392' => 'Cyprus North',
            '+357' => 'Cyprus South',
            '+42' => 'Czech Republic',
            '+45' => 'Denmark',
            '+253' => 'Djibouti',
            '+1809' => 'Dominica',
            '+1809' => 'Dominican Republic',
            '+593' => 'Ecuador',
            '+20' => 'Egypt',
            '+503' => 'El Salvador',
            '+240' => 'Equatorial Guinea',
            '+291' => 'Eritrea',
            '+372' => 'Estonia',
            '+251' => 'Ethiopia',
            '+500' => 'Falkland Islands',
            '+298' => 'Faroe Islands',
            '+679' => 'Fiji',
            '+358' => 'Finland',
            '+33' => 'France',
            '+594' => 'French Guiana',
            '+689' => 'French Polynesia',
            '+241' => 'Gabon',
            '+220' => 'Gambia',
            '+7880' => 'Georgia',
            '+49' => 'Germany',
            '+233' => 'Ghana',
            '+350' => 'Gibraltar',
            '+30' => 'Greece',
            '+299' => 'Greenland',
            '+1473' => 'Grenada',
            '+590' => 'Guadeloupe',
            '+671' => 'Guam',
            '+502' => 'Guatemala',
            '+224' => 'Guinea',
            '+245' => 'Guinea - Bissau',
            '+592' => 'Guyana',
            '+509' => 'Haiti',
            '+504' => 'Honduras',
            '+852' => 'Hong Kong',
            '+36' => 'Hungary',
            '+354' => 'Iceland',
            '+91' => 'India',
            '+62' => 'Indonesia',
            '+98' => 'Iran',
            '+964' => 'Iraq',
            '+353' => 'Ireland',
            '+972' => 'Israel',
            '+39' => 'Italy',
            '+1876' => 'Jamaica',
            '+81' => 'Japan',
            '+962' => 'Jordan',
            '+7' => 'Kazakhstan',
            '+254' => 'Kenya',
            '+686' => 'Kiribati',
            '+850' => 'Korea North',
            '+82' => 'Korea South',
            '+965' => 'Kuwait',
            '+996' => 'Kyrgyzstan',
            '+856' => 'Laos',
            '+371' => 'Latvia',
            '+961' => 'Lebanon',
            '+266' => 'Lesotho',
            '+231' => 'Liberia',
            '+218' => 'Libya',
            '+417' => 'Liechtenstein',
            '+370' => 'Lithuania',
            '+352' => 'Luxembourg',
            '+853' => 'Macao',
            '+389' => 'Macedonia',
            '+261' => 'Madagascar',
            '+265' => 'Malawi',
            '+60' => 'Malaysia',
            '+960' => 'Maldives',
            '+223' => 'Mali',
            '+356' => 'Malta',
            '+692' => 'Marshall Islands',
            '+596' => 'Martinique',
            '+222' => 'Mauritania',
            '+269' => 'Mayotte',
            '+52' => 'Mexico',
            '+691' => 'Micronesia',
            '+373' => 'Moldova',
            '+377' => 'Monaco',
            '+976' => 'Mongolia',
            '+1664' => 'Montserrat',
            '+212' => 'Morocco',
            '+258' => 'Mozambique',
            '+95' => 'Myanmar',
            '+264' => 'Namibia',
            '+674' => 'Nauru',
            '+977' => 'Nepal',
            '+31' => 'Netherlands',
            '+687' => 'New Caledonia',
            '+64' => 'New Zealand',
            '+505' => 'Nicaragua',
            '+227' => 'Niger',
            '+234' => 'Nigeria',
            '+683' => 'Niue',
            '+672' => 'Norfolk Islands',
            '+670' => 'Northern Marianas',
            '+47' => 'Norway',
            '+968' => 'Oman',
            '+680' => 'Palau',
            '+507' => 'Panama',
            '+675' => 'Papua New Guinea',
            '+595' => 'Paraguay',
            '+51' => 'Peru',
            '+63' => 'Philippines',
            '+48' => 'Poland',
            '+351' => 'Portugal',
            '+1787' => 'Puerto Rico',
            '+974' => 'Qatar',
            '+262' => 'Reunion',
            '+40' => 'Romania',
            '+7' => 'Russia',
            '+250' => 'Rwanda',
            '+378' => 'San Marino',
            '+239' => 'Sao Tome & Principe',
            '+966' => 'Saudi Arabia',
            '+221' => 'Senegal',
            '+381' => 'Serbia',
            '+248' => 'Seychelles',
            '+232' => 'Sierra Leone',
            '+65' => 'Singapore',
            '+421' => 'Slovak Republic',
            '+386' => 'Slovenia',
            '+677' => 'Solomon Islands',
            '+252' => 'Somalia',
            '+27' => 'South Africa',
            '+34' => 'Spain',
            '+94' => 'Sri Lanka',
            '+290' => 'St. Helena',
            '+1869' => 'St. Kitts',
            '+1758' => 'St. Lucia',
            '+249' => 'Sudan',
            '+597' => 'Suriname',
            '+268' => 'Swaziland',
            '+46' => 'Sweden',
            '+41' => 'Switzerland',
            '+963' => 'Syria',
            '+886' => 'Taiwan',
            '+7' => 'Tajikstan',
            '+66' => 'Thailand',
            '+228' => 'Togo',
            '+676' => 'Tonga',
            '+1868' => 'Trinidad & Tobago',
            '+216' => 'Tunisia',
            '+90' => 'Turkey',
            '+7' => 'Turkmenistan',
            '+993' => 'Turkmenistan',
            '+1649' => 'Turks & Caicos Islands',
            '+688' => 'Tuvalu',
            '+256' => 'Uganda',
            '+380' => 'Ukraine',
            '+971' => 'United Arab Emirates',
            '+598' => 'Uruguay',
            '+7' => 'Uzbekistan',
            '+678' => 'Vanuatu',
            '+379' => 'Vatican City',
            '+58' => 'Venezuela',
            '+84' => 'Vietnam',
            '+84' => 'Virgin Islands - British',
            '+84' => 'Virgin Islands - US',
            '+681' => 'Wallis & Futuna',
            '+969' => 'Yemen (North)',
            '+967' => 'Yemen (South)',
            '+260' => 'Zambia',
            '+263' => 'Zimbabwe',
        ];

        $key = array_search($countryName,$countryCodes);
        var_dump($key,"hello"); die;

    }


}
