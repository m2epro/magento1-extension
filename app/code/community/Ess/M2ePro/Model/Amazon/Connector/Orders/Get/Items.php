<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Orders_Get_Items extends Ess_M2ePro_Model_Amazon_Connector_Command_RealTime
{
    //########################################

    const TIMEOUT_ERRORS_COUNT_TO_RISE = 3;
    const TIMEOUT_RISE_ON_ERROR        = 30;
    const TIMEOUT_RISE_MAX_VALUE       = 1500;

    /** @see https://developer-docs.amazon.com/sp-api/docs/orders-api-v0-reference#addresstype */
    const AMAZON_ADDRESS_TYPE_COMMERCIAL = 'Commercial';

    //########################################

    public function getCommand()
    {
        return array('orders','get','items');
    }

    public function getRequestData()
    {
        $accountsAccessTokens = array();
        foreach ($this->_params['accounts'] as $account) {
            $accountsAccessTokens[] = $account->getChildObject()->getServerHash();
        }

        $data = array(
            'accounts' => $accountsAccessTokens,
        );

        if (!empty($this->_params['from_update_date']) && !empty($this->_params['to_update_date'])) {
            $data['from_update_date'] = $this->_params['from_update_date'];
            $data['to_update_date']   = $this->_params['to_update_date'];
        }

        if (!empty($this->_params['from_create_date']) && !empty($this->_params['to_create_date'])) {
            $data['from_create_date'] = $this->_params['from_create_date'];
            $data['to_create_date']   = $this->_params['to_create_date'];
        }

        if (!empty($this->_params['job_token'])) {
            $data['job_token'] = $this->_params['job_token'];
        }

        return $data;
    }

    //########################################

    public function process()
    {
        try {
            parent::process();
        } catch (Ess_M2ePro_Model_Exception_Connection $exception) {
            $data = $exception->getAdditionalData();
            if (!empty($data['curl_error_number']) && $data['curl_error_number'] == CURLE_OPERATION_TIMEOUTED) {
                $fails = (int)Mage::helper('M2ePro/Module')->getRegistry()->getValue(
                    '/amazon/orders/receive/timeout_fails/'
                );
                $fails++;

                $rise = (int)Mage::helper('M2ePro/Module')->getRegistry()->getValue(
                    '/amazon/orders/receive/timeout_rise/'
                );
                $rise += self::TIMEOUT_RISE_ON_ERROR;

                if ($fails >= self::TIMEOUT_ERRORS_COUNT_TO_RISE && $rise <= self::TIMEOUT_RISE_MAX_VALUE) {
                    $fails = 0;
                    Mage::helper('M2ePro/Module')->getRegistry()->setValue(
                        '/amazon/orders/receive/timeout_rise/',
                        $rise
                    );
                }

                Mage::helper('M2ePro/Module')->getRegistry()->setValue(
                    '/amazon/orders/receive/timeout_fails/',
                    $fails
                );
            }

            throw $exception;
        }

        Mage::helper('M2ePro/Module')->getRegistry()->setValue('/amazon/orders/receive/timeout_fails/', 0);
    }

    protected function buildConnectionInstance()
    {
        $connection = parent::buildConnectionInstance();
        $connection->setTimeout($this->getRequestTimeOut());

        return $connection;
    }

    //########################################

    protected function getRequestTimeOut()
    {
        $rise = (int)Mage::helper('M2ePro/Module')->getRegistry()->getValue(
            '/amazon/orders/receive/timeout_rise/'
        );
        $rise > self::TIMEOUT_RISE_MAX_VALUE && $rise = self::TIMEOUT_RISE_MAX_VALUE;

        return 300 + $rise;
    }

    //########################################

    protected function prepareResponseData()
    {
        $responseData = $this->getResponse()->getData();

        if ($this->getResponse()->isResultError() || !isset($responseData['items'])) {
            return;
        }

        $accounts = array();
        foreach ($this->_params['accounts'] as $item) {
            $accounts[$item->getChildObject()->getServerHash()] = $item;
        }

        $preparedOrders = array();

        foreach ($responseData['items'] as $accountAccessToken => $ordersData) {
            if (empty($accounts[$accountAccessToken])) {
                continue;
            }

            $preparedOrders[$accountAccessToken] = array();

            /** @var $marketplace Ess_M2ePro_Model_Marketplace */
            $marketplace = $accounts[$accountAccessToken]->getChildObject()->getMarketplace();

            foreach ($ordersData as $orderData) {
                $order = array();

                $order['amazon_order_id'] = trim($orderData['id']);
                $order['status'] = trim($orderData['status']);

                $sellerOrderId = trim($orderData['seller_id']);
                $order['seller_order_id'] = empty($sellerOrderId) ? null : $sellerOrderId;

                $order['marketplace_id'] = $marketplace->getId();
                $order['is_afn_channel'] = (int)$orderData['channel']['is_afn'];
                $order['is_prime'] = (int)$orderData['is_prime'];
                $order['is_business'] = (int)$orderData['is_business'];

                $order['purchase_create_date'] = $orderData['purchase_date'];
                $order['purchase_update_date'] = $orderData['update_date'];

                $order['buyer_name'] = trim($orderData['buyer']['name']);
                $order['buyer_email'] = trim($orderData['buyer']['email']);

                $order['is_replacement'] = (int)$orderData['is_replacement'];
                $order['replaced_amazon_order_id'] = empty($orderData['replaced_order_id']) ? null :
                    trim((string)$orderData['replaced_order_id']);

                $order['qty_shipped'] = (int)$orderData['qty']['shipped'];
                $order['qty_unshipped'] = (int)$orderData['qty']['unshipped'];

                $shipping = $orderData['shipping'];

                $order['shipping_service'] = trim($shipping['level']);
                $order['shipping_price'] = isset($orderData['price']['shipping'])
                    ? (float)$orderData['price']['shipping'] : 0;

                $order['shipping_address'] = $this->parseShippingAddress($orderData, $marketplace);

                $order['shipping_date_to'] = $shipping['ship_date']['to'];
                $order['delivery_date_from'] = $shipping['delivery_date']['from'];
                $order['delivery_date_to'] = $shipping['delivery_date']['to'];

                $order['currency']    = isset($orderData['currency']) ? trim($orderData['currency']) : '';
                $order['paid_amount'] = isset($orderData['amount_paid']) ? (float)$orderData['amount_paid'] : 0;
                $order['tax_details'] = isset($orderData['price']['taxes']) ? $orderData['price']['taxes'] : array();
                $order['tax_registration_details'] = isset($orderData['tax_registration_details']) ?
                    $orderData['tax_registration_details'] : array();

                $order['is_buyer_requested_cancel'] = isset($orderData['is_buyer_requested_cancel']) ?
                    (int)$orderData['is_buyer_requested_cancel'] : 0;
                $order['buyer_cancel_reason'] = isset($orderData['buyer_cancel_reason']) ?
                    $orderData['buyer_cancel_reason'] : null;

                $order['discount_details'] = isset($orderData['price']['discounts'])
                    ? $orderData['price']['discounts'] : array();

                $order['items'] = array();

                foreach ($orderData['items'] as $item) {
                    $order['items'][] = array(
                        'amazon_order_item_id' => trim($item['id']),
                        'sku'                  => trim($item['identifiers']['sku']),
                        'general_id'           => trim($item['identifiers']['general_id']),
                        'is_isbn_general_id'   => (int)$item['identifiers']['is_isbn'],
                        'title'                => trim($item['title']),
                        'price'                => (float)$item['prices']['product']['value'],
                        'shipping_price'       => (float)$item['prices']['shipping']['value'],
                        'gift_price'           => (float)$item['prices']['gift']['value'],
                        'gift_type'            => trim($item['gift_type']),
                        'gift_message'         => trim($item['gift_message']),
                        'currency'             => trim($item['prices']['product']['currency']),
                        'tax_details'          => $item['taxes'],
                        'ioss_number'          => $item['ioss_number'],
                        'discount_details'     => $item['discounts'],
                        'qty_purchased'        => (int)$item['qty']['ordered'],
                        'qty_shipped'          => (int)$item['qty']['shipped']
                    );
                }

                $preparedOrders[$accountAccessToken][] = $order;
            }
        }

        $this->_responseData = array(
            'items' => $preparedOrders,
        );

        if (!empty($responseData['to_update_date'])) {
            $this->_responseData['to_update_date'] = $responseData['to_update_date'];
        }

        if (!empty($responseData['to_create_date'])) {
            $this->_responseData['to_create_date'] = $responseData['to_create_date'];
        }

        if (!empty($responseData['job_token'])) {
            $this->_responseData['job_token'] = $responseData['job_token'];
        }
    }

    protected function parseShippingAddress(array $orderData, Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $shippingData = $orderData['shipping'];
        $location = isset($shippingData['location']) ? $shippingData['location'] : array();
        $address  = isset($shippingData['address']) ? $shippingData['address'] : array();

        $parsedAddress = array(
            'county'         => isset($location['county']) ? trim($location['county']) : '',
            'country_code'   => isset($location['country_code']) ? trim($location['country_code']) : '',
            'state'          => isset($location['state']) ? trim($location['state']) : '',
            'city'           => isset($location['city']) ? trim($location['city']) : '',
            'postal_code'    => isset($location['postal_code']) ? $location['postal_code'] : '',
            'recipient_name' => isset($shippingData['buyer']) ? trim($shippingData['buyer']) : '',
            'phone'          => isset($shippingData['phone']) ? $shippingData['phone'] : '',
            'company'        => isset($shippingData['company_name']) ? $shippingData['company_name'] : '',
            'address_type'   => isset($shippingData['address_type']) ? $shippingData['address_type'] : '',
            'street'         => array(
                isset($address['first']) ? $address['first'] : '',
                isset($address['second']) ? $address['second'] : '',
                isset($address['third']) ? $address['third'] : ''
            ),
            'buyer_company_name' => isset($orderData['buyer_company_name']) ? $orderData['buyer_company_name'] : '',
        );
        $parsedAddress['street'] = array_filter($parsedAddress['street']);

        $group = '/amazon/order/settings/marketplace_'.$marketplace->getId().'/';
        $useFirstStreetLineAsCompany = Mage::helper('M2ePro/Module')
            ->getConfig()
            ->getGroupValue($group, 'use_first_street_line_as_company');

        if (
            $useFirstStreetLineAsCompany
            && empty($parsedAddress['company'])
            && $parsedAddress['address_type'] === self::AMAZON_ADDRESS_TYPE_COMMERCIAL
            && count($parsedAddress['street']) > 1
        ) {
            $parsedAddress['company'] = array_shift($parsedAddress['street']);
        }

        return $parsedAddress;
    }

    //########################################
}
