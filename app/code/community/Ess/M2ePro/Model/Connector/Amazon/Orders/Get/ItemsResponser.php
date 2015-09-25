<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Amazon_Orders_Get_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Responser
{
    // ########################################

    protected function validateResponseData($response)
    {
        if (!isset($response['items'])) {
            return false;
        }

        return true;
    }

    protected function prepareResponseData($response)
    {
        /* @var $marketplace Ess_M2ePro_Model_Marketplace */
        $marketplace = $this->getObjectByParam('Account','account_id')->getChildObject()->getMarketplace();

        $preparedOrders = array();

        foreach ($response['items'] as $orderData) {

            $order = array();

            $order['amazon_order_id'] = trim($orderData['id']);
            $order['status'] = trim($orderData['status']);

            $order['marketplace_id'] = $marketplace->getId();
            $order['is_afn_channel'] = (int)$orderData['channel']['is_afn'];

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

            $order['currency'] = isset($orderData['currency']) ? trim($orderData['currency']) : '';
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

            $preparedOrders[] = $order;
        }

        return $preparedOrders;
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