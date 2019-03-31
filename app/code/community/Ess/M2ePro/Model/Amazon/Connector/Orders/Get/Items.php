<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Orders_Get_Items extends Ess_M2ePro_Model_Amazon_Connector_Command_RealTime
{
    // ########################################

    const TIMEOUT_ERRORS_COUNT_TO_RISE = 3;
    const TIMEOUT_RISE_ON_ERROR        = 30;
    const TIMEOUT_RISE_MAX_VALUE       = 1500;

    //########################################

    public function getCommand()
    {
        return array('orders','get','items');
    }

    public function getRequestData()
    {
        $accountsAccessTokens = array();
        foreach ($this->params['accounts'] as $account) {
            $accountsAccessTokens[] = $account->getChildObject()->getServerHash();
        }

        $data = array(
            'accounts'         => $accountsAccessTokens,
            'from_update_date' => $this->params['from_update_date'],
            'to_update_date'   => $this->params['to_update_date']
        );

        if (!empty($this->params['job_token'])) {
            $data['job_token'] = $this->params['job_token'];
        }

        return $data;
    }

    //########################################

    public function process()
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfigGroup = '/amazon/synchronization/orders/receive/timeout';

        try {

            parent::process();

        } catch (Ess_M2ePro_Model_Exception_Connection $exception) {

            $data = $exception->getAdditionalData();
            if (!empty($data['curl_error_number']) && $data['curl_error_number'] == CURLE_OPERATION_TIMEOUTED) {

                $fails = (int)$cacheConfig->getGroupValue($cacheConfigGroup, 'fails');
                $fails++;

                $rise = (int)$cacheConfig->getGroupValue($cacheConfigGroup, 'rise');
                $rise += self::TIMEOUT_RISE_ON_ERROR;

                if ($fails >= self::TIMEOUT_ERRORS_COUNT_TO_RISE && $rise <= self::TIMEOUT_RISE_MAX_VALUE) {

                    $fails = 0;
                    $cacheConfig->setGroupValue($cacheConfigGroup, 'rise', $rise);
                }
                $cacheConfig->setGroupValue($cacheConfigGroup, 'fails', $fails);
            }

            throw $exception;
        }

        $cacheConfig->setGroupValue($cacheConfigGroup, 'fails', 0);
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
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfigGroup = '/amazon/synchronization/orders/receive/timeout';

        $rise = (int)$cacheConfig->getGroupValue($cacheConfigGroup, 'rise');
        $rise > self::TIMEOUT_RISE_MAX_VALUE && $rise = self::TIMEOUT_RISE_MAX_VALUE;

        return 300 + $rise;
    }

    // ########################################

    protected function prepareResponseData()
    {
        $responseData = $this->getResponse()->getData();

        if ($this->getResponse()->isResultError() || !isset($responseData['items'])) {
            return;
        }

        $accounts = array();
        foreach ($this->params['accounts'] as $item) {
            $accounts[$item->getChildObject()->getServerHash()] = $item;
        }

        $preparedOrders = array();

        foreach ($responseData['items'] as $accountAccessToken => $ordersData) {

            if (empty($accounts[$accountAccessToken])) {
                continue;
            }

            $preparedOrders[$accountAccessToken] = array();

            /* @var $marketplace Ess_M2ePro_Model_Marketplace */
            $marketplace = $accounts[$accountAccessToken]->getChildObject()->getMarketplace();

            foreach ($ordersData as $orderData) {

                $order = array();

                $order['amazon_order_id'] = trim($orderData['id']);
                $order['status'] = trim($orderData['status']);

                $sellerOrderId = trim($orderData['seller_id']);
                $order['seller_order_id'] = empty($sellerOrderId) ? NULL : $sellerOrderId;

                $order['marketplace_id'] = $marketplace->getId();
                $order['is_afn_channel'] = (int)$orderData['channel']['is_afn'];
                $order['is_prime'] = (int)$orderData['is_prime'];
                $order['is_business'] = (int)$orderData['is_business'];

                $order['purchase_create_date'] = $orderData['purchase_date'];
                $order['purchase_update_date'] = $orderData['update_date'];

                $order['buyer_name'] = trim($orderData['buyer']['name']);
                $order['buyer_email'] = trim($orderData['buyer']['email']);

                $order['qty_shipped'] = (int)$orderData['qty']['shipped'];
                $order['qty_unshipped'] = (int)$orderData['qty']['unshipped'];

                $shipping = $orderData['shipping'];

                $order['shipping_service'] = trim($shipping['level']);
                $order['shipping_price'] = isset($orderData['price']['shipping'])
                    ? (float)$orderData['price']['shipping'] : 0;

                $order['shipping_address'] = $this->parseShippingAddress($shipping, $marketplace);

                $order['shipping_dates'] = array(
                    'ship' => array(
                        'from' => $shipping['ship_date']['from'],
                        'to'   => $shipping['ship_date']['to'],
                    ),
                    'delivery' => array(
                        'from' => $shipping['delivery_date']['from'],
                        'to'   => $shipping['delivery_date']['to'],
                    ),
                );

                $order['currency']    = isset($orderData['currency']) ? trim($orderData['currency']) : '';
                $order['paid_amount'] = isset($orderData['amount_paid']) ? (float)$orderData['amount_paid'] : 0;
                $order['tax_details'] = isset($orderData['price']['taxes']) ? $orderData['price']['taxes'] : array();

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
                        'gift_price'           => (float)$item['prices']['gift']['value'],
                        'gift_type'            => trim($item['gift_type']),
                        'gift_message'         => trim($item['gift_message']),
                        'currency'             => trim($item['prices']['product']['currency']),
                        'tax_details'          => $item['taxes'],
                        'discount_details'     => $item['discounts'],
                        'qty_purchased'        => (int)$item['qty']['ordered'],
                        'qty_shipped'          => (int)$item['qty']['shipped']
                    );
                }

                $preparedOrders[$accountAccessToken][] = $order;
            }
        }

        $this->responseData = array(
            'items'          => $preparedOrders,
            'to_update_date' => $responseData['to_update_date']
        );

        if (!empty($responseData['job_token'])) {
            $this->responseData['job_token'] = $responseData['job_token'];
        }
    }

    private function parseShippingAddress(array $shippingData, Ess_M2ePro_Model_Marketplace $marketplace)
    {
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
            'company'        => '',
            'street'         => array(
                isset($address['first']) ? $address['first'] : '',
                isset($address['second']) ? $address['second'] : '',
                isset($address['third']) ? $address['third'] : ''
            )
        );
        $parsedAddress['street'] = array_filter($parsedAddress['street']);

        $group = '/amazon/order/settings/marketplace_'.$marketplace->getId().'/';
        $useFirstStreetLineAsCompany = Mage::helper('M2ePro/Module')
            ->getConfig()
            ->getGroupValue($group, 'use_first_street_line_as_company');

        if ($useFirstStreetLineAsCompany && count($parsedAddress['street']) > 1) {
            $parsedAddress['company'] = array_shift($parsedAddress['street']);
        }

        return $parsedAddress;
    }

    // ########################################
}