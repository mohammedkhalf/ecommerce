<?php

/**
 * Class that handles server callbacks (webhooks)
 */
class Cowpay_Server_Callback
{
    private $settings;

    function __construct()
    {
        $this->settings = Cowpay_Admin_Settings::getInstance();
    }

    /**
     * Should fired in wordpress init
     */
    public function update_order()
    {
        if (!$this->is_cowpay_callback()) return; // die peacely if we are not the target
        $data = $this->get_callback_request_data();
        (wc_get_logger())->info(wc_print_r( $data, true)."\n" , array('source' => 'cowpay-webhook'));
        if (!$data) return $this->exit_error("not valid callback");
        // $checkSign = $this->is_valid_signature($data);
        // if (!$this->is_valid_signature($data)) return $this->exit_error("not valid signature");
        $order_status = strtoupper($this->getOrderStatus($data['statusId']));

        switch ($order_status) {
            case 'PENDING':
                // order created successfully
                $this->handle_order_creation($data);
                break;

            case 'UNPAID':
                $this->handle_unpaid($data);
                break;
            case 'PAID':
                $this->handle_paid($data);
                break;

            case 'EXPIRED':
                $this->handle_expired($data);
                break;

            case 'FAILED':
                $this->handle_failed($data);
                break;
            case 'REFUNDED':
                $this->handle_refund($data);
                break;
            case 'DELIVERED':
                // we are not handling cash-collection in this plugin yet
                break;
            default:
                return $this->exit_error("unknown callback request type '$callback_type'");
        }

        wp_die("callback successfully handled", 200);
    }


    private function is_cowpay_callback()
    {
        // only check for url params, ~/?action=cowpay
        return isset($_GET["action"]) && $_GET["action"] == "cowpay";
    }
    /**
     * Returns data of the callback request
     * or false on not valid requests
     */
    private function get_callback_request_data()
    {
        // get post data payload
        $data = json_decode(file_get_contents('php://input'), true);
        (wc_get_logger())->info(wc_print_r( $data, true)."\n" , array('source' => 'cowpay-webhook'));
        // empty data?
        if (!isset($data) || empty($data)) return false;

        $customData = [
            "merchant_code" =>$data['merchantCode'],
            "cowpay_reference_id" =>$data['cowpayReferenceId'],
            "merchant_reference_id" => $data['merchantReferenceId'],
            "payment_gateway_reference_id" => $data['paymentGatewayReferenceId'],
            "order_status" => $data['orderStatus'],
            "amount" => $data['amount'],
            "paymentMethod" => $data['paymentMethod'],
            'statusId' => $data['statusId']
        ];
        // check required fields
        $required_data_keys = array("merchant_code","cowpay_reference_id", "merchant_reference_id", "order_status", "amount",'paymentMethod','statusId');
        foreach ($required_data_keys as $key) if (!isset($customData[$key])) return false;
        // we are safe now
        return $customData;
    }

    /**
     * Handle logic of order creation server callback
     * creates the order if doesn't created before in the charge request
     */
    private function handle_order_creation($data)
    {
        $merchant_reference_id = $data["merchant_reference_id"];
        $order = $this->find_order($merchant_reference_id);
        if ($order !== false) {
            // order already exists
            $order->add_order_note(__("server callback update: created at the server",'woo-cowpay'));
            return;
        }
        $this->create_order_recovery($data);
    }

    /**
     * try to create order from callback data,
     * this is a type of error recovery if order doesn't created
     * successfully at charge request time
     */
    private function create_order_recovery($data)
    {
        $merchant_reference_id = $data["merchant_reference_id"];
        $order = wc_create_order(array('status' => "wc-processing"));
        $order->add_meta_data("cp_merchant_reference_id", $merchant_reference_id);
        $order->add_meta_data("cp_amount", $data['amount']);
        $order->add_meta_data("cp_cowpay_reference_id", $data['cowpay_reference_id']);
        $order->add_order_note(esc_html__("Order created using server callback",'woo-cowpay'));
        return $order;
    }

    /**
     * find order using merchant reference id
     */
    public function find_order($id)
    {
        $order = wc_get_orders(array('cp_merchant_reference_id' => $id, 'limit' => 1));   // cp_merchantReferenceId
        if (empty($order)) return false;
        return $order[0];
    }

    private function handle_paid($data)
    {
        $merchant_reference_id = $data["merchant_reference_id"];
        $order = $this->find_order($merchant_reference_id);
        if ($order == false) {
            // TODO: log a warning message
            // try to recover if order is not created before
            $order = $this->create_order_recovery($data);
        }
        $order->payment_complete();
        $admin_complete_order_status = $this->settings->get_order_status();
        $order->update_status($admin_complete_order_status);
        $order->add_order_note(esc_html__('server callback update: Successfully paid','woo-cowpay'));
    }

    private function handle_unpaid($data)
    {
        $merchant_reference_id = $data["merchant_reference_id"];
        $order = $this->find_order($merchant_reference_id);
        if ($order == false) {
            // TODO: log a warning message
            // don't create order as it is already expired

            return;
        }
        $order->update_status("wc-processing");
        $order->add_order_note(__('server callback update: The order was unpaid','woo-cowpay'));
    }

    private function handle_expired($data)
    {
        $merchant_reference_id = $data["merchant_reference_id"];
        $order = $this->find_order($merchant_reference_id);
        if ($order == false) {
            // TODO: log a warning message
            // don't create order as it is already expired
            return;
        }
        $order->update_status("wc-cancelled");
        $order->add_order_note(__('server callback update: The order was expired','woo-cowpay'));
    }

    private function handle_failed($data)
    {
        $merchant_reference_id = $data["merchant_reference_id"];
        $order = $this->find_order($merchant_reference_id);
        if ($order == false) {
            // TODO: log a warning message
            // don't create order as it is already failed
            return;
        }
        $order->update_status("wc-cancelled");
        $order->add_order_note(esc_html__('server callback update: The order was failed','woo-cowpay'));
    }

    private function handle_refund($data)
    {
        $merchant_reference_id = $data["merchant_reference_id"];
        $order = $this->find_order($merchant_reference_id);
        if ($order == false) {
            // TODO: log a warning message
            // don't create order as it is already failed
            return;
        }
        $order->update_status("wc-cancelled");
        $order->add_order_note(esc_html__('server callback update: The order was refunded','woo-cowpay'));
    }

    private function is_valid_signature($payload)
    {
        $callbackSign = md5("{$payload["merchantCode"]}{$payload["amount"]}{$payload["cowpay_reference_id"]}{$payload["merchant_reference_id"]}{$payload["order_status"]}");
        return $callbackSign;
    }

    private function is_valid_signature_backup($payload)
    {
        $hypo_arr = array(
            $this->settings->get_merchant_hash(),
            $payload["amount"],
            $payload["cowpay_reference_id"],
            $payload["merchant_reference_id"],
            $payload["order_status"]
        );
        return md5(join('', $hypo_arr)) === $payload['signature'];
    }

    /**
     * End with error
     */
    private function exit_error($cause)
    {
        // echo json_encode(array('error' => $cause, 'success' => false));
        wp_die($cause, 400);
    }

    public function getOrderStatus($statusId)
    {
        $orderStatus = [
            1 =>'Pending',
            2 =>'Paid',
            3 =>'UnPaid',
            4 =>'Expired',
            5 =>'Failed',
            6 =>'Refunded',
            7 =>'PartiallyRefunded',
        ];

        $order_status = isset($orderStatus[$statusId]) ? $orderStatus[$statusId] : null;
        return  $order_status;
    }
}
