<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Amazon_Order_MerchantFulfillmentController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    public function getPopupAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        if (is_null($orderId)) {
            return $this->getResponse()->setBody('You should specify order ID');
        }

        /** @var Ess_M2ePro_Model_Order $order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Order', $orderId
        );

        $orderFulfillmentData = $order->getChildObject()->getMerchantFulfillmentData();
        $orderItems = $order->getItemsCollection()->getItems();

        $responseData = array(
            'status' => true,
            'html'   => ''
        );

        if (!empty($orderFulfillmentData)) {
            $popUp = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_common_amazon_order_merchantFulfillment_information');

            $popUp->setData('fulfillment_details', $orderFulfillmentData);
            $popUp->setData('order_items', $orderItems);
            $popUp->setData('fulfillment_not_wizard', true);

        } elseif (!$order->getMarketplace()->getChildObject()->isMerchantFulfillmentAvailable()) {
            $popUp = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_common_amazon_order_merchantFulfillment_message');
            $popUp->setData('message', 'marketplaceError');
            $responseData['status'] = false;

        } elseif ($order->getChildObject()->isFulfilledByAmazon()) {
            $popUp = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_common_amazon_order_merchantFulfillment_message');
            $popUp->setData('message', 'fbaError');
            $responseData['status'] = false;

        } elseif ($order->getChildObject()->isCanceled() || $order->getChildObject()->isPending()) {
            $popUp = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_common_amazon_order_merchantFulfillment_message');
            $popUp->setData('message', 'statusError');
            $responseData['status'] = false;

        } else {
            $popUp = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_common_amazon_order_merchantFulfillment_configuration');

            $popUp->setData('order_items', $orderItems);
            $popUp->setData('order_currency', $order->getChildObject()->getCurrency());
            $popUp->setData('declared_value', $order->getChildObject()->getSubtotalPrice());
            $popUp->setData('delivery_date_to', $order->getChildObject()->getDeliveryDateTo());
        }

        $responseData['html'] = $popUp->toHtml();
        return $this->getResponse()->setBody(json_encode($responseData));
    }

    public function getShippingServicesAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        if (is_null($orderId)) {
            return $this->getResponse()->setBody('You should specify order ID');
        }

        $post = $this->getRequest()->getPost();

        if (empty($post)) {
            return $this->getResponse()->setBody('You should specify POST data');
        }

        $fulfillmentCachedFields = array(
            'package_dimension_measure',
            'package_weight_measure',
            'ship_from_address_name',
            'ship_from_address_email',
            'ship_from_address_phone',
            'ship_from_address_country',
            'ship_from_address_region_state',
            'ship_from_address_postal_code',
            'ship_from_address_city',
            'ship_from_address_address_line_1',
            'ship_from_address_address_line_2',
            'delivery_experience',
            'carrier_will_pickup'
        );

        $fulfillmentCachedData = array_intersect_key($post, array_flip($fulfillmentCachedFields));

        Mage::helper('M2ePro/Data_Cache_Permanent')->setValue('amazon_merchant_fulfillment_data',
            $fulfillmentCachedData,
            array('amazon', 'merchant_fulfillment')
        );

        $popup = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_order_merchantFulfillment_shippingServices');

        /** @var Ess_M2ePro_Model_Order $order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Order', $orderId
        );

        $orderItems = $order->getItemsCollection()->getItems();
        $preparedOrderItems = array();
        foreach ($orderItems as $parentOrderItem) {
            $orderItem = $parentOrderItem->getChildObject();
            $preparedOrderItems[] = array(
                'id'  => $orderItem->getAmazonOrderItemId(),
                'qty' => $orderItem->getQtyPurchased()
            );
        }

        $preparedPackageData = array();
        if ($post['package_dimension_source']
            == Ess_M2ePro_Helper_Component_Amazon_MerchantFulfillment::DIMENSION_SOURCE_PREDEFINED){
            $preparedPackageData['predefined_dimensions'] = $post['package_dimension_predefined'];
        } elseif($post['package_dimension_source']
            == Ess_M2ePro_Helper_Component_Amazon_MerchantFulfillment::DIMENSION_SOURCE_CUSTOM){
            $preparedPackageData['dimensions']  = array();
            $preparedPackageData['dimensions']['length']          = $post['package_dimension_length'];
            $preparedPackageData['dimensions']['width']           = $post['package_dimension_width'];
            $preparedPackageData['dimensions']['height']          = $post['package_dimension_height'];
            $preparedPackageData['dimensions']['unit_of_measure'] = $post['package_dimension_measure'];
        }

        $preparedPackageData['weight'] = array(
            'value'           => $post['package_weight'],
            'unit_of_measure' => $post['package_weight_measure']
        );

        $preparedShipmentData = array();
        $preparedShipmentData['info'] = array(
            'name'  => $post['ship_from_address_name'],
            'email' => $post['ship_from_address_email'],
            'phone' => $post['ship_from_address_phone'],
        );
        $preparedShipmentData['physical'] = array(
            'country'      => $post['ship_from_address_country'],
            'city'         => $post['ship_from_address_city'],
            'postal_code'  => $post['ship_from_address_postal_code'],
            'address_1'    => $post['ship_from_address_address_line_1'],
        );

        if ($post['ship_from_address_region_state']) {
            $preparedShipmentData['physical']['region_state'] = $post['ship_from_address_region_state'];
        }

        if ($post['ship_from_address_address_line_2']) {
            $preparedShipmentData['physical']['address_2'] = $post['ship_from_address_address_line_2'];
        }

        $requestData = array(
            'order_id'                    => $order->getChildObject()->getAmazonOrderId(),
            'order_items'                 => $preparedOrderItems,
            'package'                     => $preparedPackageData,
            'shipment_location'           => $preparedShipmentData,
            'delivery_confirmation_level' => $post['delivery_experience'],
            'carrier_pickup'              => $post['carrier_will_pickup']
        );

        if ($post['must_arrive_by_date']) {
            $mustArriveByDateTimestamp = strtotime($post['must_arrive_by_date']);
            $mustArriveByDate = new DateTime();
            $mustArriveByDate->setTimestamp($mustArriveByDateTimestamp);
            $requestData['arrive_by_date'] = $mustArriveByDate->format(DATE_ISO8601);
        }

        if ($post['declared_value']) {
            $requestData['declared_value']['amount'] = $post['declared_value'];
            $requestData['declared_value']['currency_code'] = $order->getChildObject()->getCurrency();
        }

        Mage::helper('M2ePro/Data_Session')->setValue('fulfillment_request_data', $requestData);

        try {
            $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('shipment', 'get', 'offers',
                $requestData, NULL, $order->getAccount());

            $response = $dispatcherObject->process($connectorObj);
            $popup->setData('shipping_services', $response);

        } catch (Exception $exception) {
            $popup->setData('error_message', $exception->getMessage());
        }

        return $this->getResponse()->setBody($popup->toHtml());
    }

    public function createShippingOfferAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        if (is_null($orderId)) {
            return $this->getResponse()->setBody('You should specify order ID');
        }

        $post = $this->getRequest()->getPost();

        if (empty($post)) {
            return $this->getResponse()->setBody('You should specify POST data');
        }

        if (!$post['shipping_service_id']){
            return $this->getResponse()->setBody('You should choose shipping service');
        }

        $requestData = Mage::helper('M2ePro/Data_Session')->getValue('fulfillment_request_data');

        if (is_null($requestData)) {
            return $this->getResponse()->setBody('You should get eligible shipping services on previous step');
        }

        $requestData['shipping_service_id'] = $post['shipping_service_id'];

        /** @var Ess_M2ePro_Model_Order $order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Order', $orderId
        );

        $popup = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_amazon_order_merchantFulfillment_information');

        try {
            $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('shipment', 'add', 'entity',
                $requestData, NULL, $order->getAccount());

            $response = $dispatcherObject->process($connectorObj);

            $labelContent = $response['label']['file']['contents'];
            $labelContent = base64_decode($labelContent);
            $labelContent = gzdecode($labelContent);

            unset($response['label']['file']['contents']);

            $order->addData(array(
                'merchant_fulfillment_data'  => json_encode($response),
                'merchant_fulfillment_label' => $labelContent,
            ))->save();

            $popup->setData('fulfillment_details', $response);

            $orderItems = $order->getItemsCollection()->getItems();
            $popup->setData('order_items', $orderItems);

        } catch (Exception $exception) {
            $popup->setData('error_message', $exception->getMessage());
        }

        return $this->getResponse()->setBody($popup->toHtml());
    }

    public function refreshDataAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        if (is_null($orderId)) {
            return $this->getResponse()->setBody('You should specify order ID');
        }

        /** @var Ess_M2ePro_Model_Order $order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Order', $orderId
        );

        $orderFulfillmentData = $order->getChildObject()->getMerchantFulfillmentData();

        if (empty($orderFulfillmentData)) {
            return $this->getResponse()->setBody('You should create shipment first');
        }

        $requestData = array(
            'shipment_id' => $orderFulfillmentData['shipment_id']
        );

        $responseData = array(
            'success' => false
        );

        try {
            $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('shipment', 'get', 'entity',
                $requestData, NULL, $order->getAccount());

            $response = $dispatcherObject->process($connectorObj);

            if (empty($response['label']) && !empty($orderFulfillmentData['label'])) {
                $order->setData('merchant_fulfillment_label', NULL);
            }
            unset($response['label']['file']['contents']);

            $order->setSettings('merchant_fulfillment_data', $response)->save();
            $responseData['success'] = true;
        } catch (Exception $exception) {
            $responseData['error_message'] = $exception->getMessage();
        }

        return $this->getResponse()->setBody(json_encode($responseData));
    }

    public function cancelShippingOfferAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        if (is_null($orderId)) {
            return $this->getResponse()->setBody('You should specify order ID');
        }

        /** @var Ess_M2ePro_Model_Order $order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Order', $orderId
        );

        $orderFulfillmentData = $order->getChildObject()->getMerchantFulfillmentData();

        if (empty($orderFulfillmentData)) {
            return $this->getResponse()->setBody('You should create shipment first');
        }

        $statusRefundPurchased = Ess_M2ePro_Helper_Component_Amazon_MerchantFulfillment::STATUS_PURCHASED;

        if ($orderFulfillmentData['status'] != $statusRefundPurchased) {
            return $this->getResponse()->setBody('Shipment status should be Purchased');
        }

        $requestData = array(
            'shipment_id' => $orderFulfillmentData['shipment_id']
        );

        $responseData = array(
            'success' => false
        );

        try {
            $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('shipment', 'cancel', 'entity',
                $requestData, NULL, $order->getAccount());

            $response = $dispatcherObject->process($connectorObj);

            if (empty($response['label']) && !empty($orderFulfillmentData['label'])) {
                $order->setData('merchant_fulfillment_label', NULL);
            }

            $order->setSettings('merchant_fulfillment_data', $response)->save();
            $responseData['success'] = true;

        } catch (Exception $exception) {
            $responseData['error_message'] = $exception->getMessage();
        }

        return $this->getResponse()->setBody(json_encode($responseData));
    }

    public function resetDataAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        if (is_null($orderId)) {
            return $this->getResponse()->setBody('You should specify order ID');
        }

        /** @var Ess_M2ePro_Model_Order $order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Order', $orderId
        );

        $orderFulfillmentData = $order->getChildObject()->getMerchantFulfillmentData();

        if (empty($orderFulfillmentData)) {
            return $this->getResponse()->setBody('You should create shipment first');
        }

        if ($orderFulfillmentData['status']
            == Ess_M2ePro_Helper_Component_Amazon_MerchantFulfillment::STATUS_PURCHASED) {
            return $this->getResponse()->setBody('Shipment status should not be Purchased');
        }

        $order->addData(array(
            'merchant_fulfillment_data'  => NULL,
            'merchant_fulfillment_label' => NULL
        ))->save();

        $responseData = array(
            'success' => true
        );

        return $this->getResponse()->setBody(json_encode($responseData));
    }

    public function getLabelAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        if (is_null($orderId)) {
            return $this->getResponse()->setBody('You should specify order ID');
        }

        /** @var Ess_M2ePro_Model_Order $order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Order', $orderId
        );

        $orderFulfillmentData = $order->getChildObject()->getMerchantFulfillmentData();
        $labelContent = $order->getChildObject()->getData('merchant_fulfillment_label');

        if (empty($orderFulfillmentData['label']) || is_null($labelContent)) {
            return $this->getResponse()->setBody('The shipment has no label');
        }

        $this->getResponse()->setHeader('Content-type', $orderFulfillmentData['label']['file']['type']);
        return $this->getResponse()->setBody($labelContent);
    }

    //########################################

    public function discardMagentoNotificationPopupAction()
    {
        $registry = Mage::getModel('M2ePro/Registry');
        $registry->load('/amazon/order/merchant_fulfillment/disable_notification_popup/', 'key');

        $registry->setData(array(
            'key'   => '/amazon/order/merchant_fulfillment/disable_notification_popup/',
            'value' => 1,
        ));

        $registry->save();
    }

    //########################################

    public function markAsShippedAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        if (is_null($orderId)) {
            return $this->getResponse()->setBody('You should specify order ID');
        }

        /** @var Ess_M2ePro_Model_Order $order */
        $order = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Order', $orderId
        );

        $responseData = array(
            'success' => true,
        );

        if ($order->getChildObject()->isPrime()) {
            $popUp = $this->loadLayout()->getLayout()
                ->createBlock('M2ePro/adminhtml_common_amazon_order_merchantFulfillment_message');
            $popUp->setData('message', 'markAsShipped');
            $responseData['html'] = $popUp->toHtml();
            $responseData['success'] = false;
        }

        return $this->getResponse()->setBody(json_encode($responseData));
    }

    //########################################
}