<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Walmart_OrderController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Orders'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Plugin/ActionColumn.js')
             ->addJs('M2ePro/Order/Debug.js')
             ->addJs('M2ePro/Order/Handler.js')
             ->addJs('M2ePro/Order/Edit/ItemHandler.js')
             ->addJs('M2ePro/Order/Edit/ShippingAddressHandler.js');

        $this->setPageHelpLink(NULL, NULL, "x/L4taAQ");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Walmart::MENU_ROOT_NODE_NICK . '/orders'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_walmart_order'));

        $this->renderLayout();
    }

    public function gridAction()
    {
        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_order_grid')
            ->toHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Walmart')->getObject('Order', (int)$id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $order);

        $this->_initAction();

        $this->setPageHelpLink(NULL, NULL, "x/L4taAQ");

        $this->_initPopUp();
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_walmart_order_view'))
            ->renderLayout();
    }

    //########################################

    public function orderItemGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Walmart')->getObject('Order', (int)$id);

        if (!$id || !$order->getId()) {
            return;
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $order);

        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_walmart_order_view_item')
            ->toHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function createMagentoOrderAction()
    {
        $id = $this->getRequest()->getParam('id');
        $force = $this->getRequest()->getParam('force');

        /** @var $order Ess_M2ePro_Model_Order */
        $order = Mage::helper('M2ePro/Component_Walmart')->getObject('Order', (int)$id);
        $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);

        // M2ePro_TRANSLATIONS
        // Magento Order is already created for this Walmart Order.
        if (!is_null($order->getMagentoOrderId()) && $force != 'yes') {
            $message = 'Magento Order is already created for this Walmart Order. ' .
                       'Press Create Order Button to create new one.';

            $this->_getSession()->addWarning(
                Mage::helper('M2ePro')->__($message)
            );
            $this->_redirect('*/*/view', array('id' => $id));
            return;
        }

        // Create magento order
        // ---------------------------------------
        try {
            $order->createMagentoOrder();
            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Magento Order was created.'));
        } catch (Exception $e) {
            $message = Mage::helper('M2ePro')->__(
                'Magento Order was not created. Reason: %error_message%',
                Mage::helper('M2ePro/Module_Log')->decodeDescription($e->getMessage())
            );
            $this->_getSession()->addError($message);
        }
        // ---------------------------------------

        // Create invoice
        // ---------------------------------------
        if ($order->getChildObject()->canCreateInvoice()) {
            $result = $order->createInvoice();
            $result && $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Invoice was created.'));
        }
        // ---------------------------------------

        // Create shipment
        // ---------------------------------------
        if ($order->getChildObject()->canCreateShipment()) {
            $result = $order->createShipment();
            $result && $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Shipment was created.'));
        }
        // ---------------------------------------

        // ---------------------------------------
        $order->updateMagentoOrderStatus();
        // ---------------------------------------

        $this->_redirect('*/*/view', array('id' => $id));
    }

    //########################################

    public function editShippingAddressAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Walmart')->getObject('Order', (int)$id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $order);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_walmart_order_edit_shippingAddress'))
             ->renderLayout();
    }

    public function saveShippingAddressAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/adminhtml_walmart_order/index');
        }

        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::helper('M2ePro/Component_Walmart')->getObject('Order', (int)$id);

        $data = array();
        $keys = array(
            'buyer_name',
            'buyer_email'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $order->setData('buyer_name', $data['buyer_name']);
        $order->setData('buyer_email', $data['buyer_email']);

        $data = array();
        $keys = array(
            'county',
            'country_code',
            'state',
            'city',
            'postal_code',
            'recipient_name',
            'phone',
            'street'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        if (isset($data['street']) && is_array($data['street'])) {
            $data['street'] = array_filter($data['street']);
        }

        $oldShippingAddress = $order->getSettings('shipping_address');
        if (empty($data['recipient_name'])) {
            $data['recipient_name'] = !empty($oldShippingAddress['recipient_name'])
                ? $oldShippingAddress['recipient_name'] : NULL;
        }

        $order->setSettings('shipping_address', $data);
        $order->save();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Order address has been updated.'));

        $this->_redirect('*/adminhtml_walmart_order/view', array('id' => $order->getId()));
    }

    //########################################

    public function updateShippingStatusAction()
    {
        $ids = $this->getRequestIds();

        if (count($ids) == 0) {

            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Order(s).'));
            $this->_redirect('*/*/index');
            return;
        }

        /** @var Ess_M2ePro_Model_Mysql4_Order_Collection $ordersCollection */
        $ordersCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Order')
            ->addFieldToFilter('id', array('in' => $ids));

        $hasFailed = false;
        $hasSucceeded = false;

        foreach ($ordersCollection->getItems() as $order) {
            /** @var Ess_M2ePro_Model_Order $order */

            $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);

            $shipmentsCollection = Mage::getResourceModel('sales/order_shipment_collection')
                ->setOrderFilter($order->getMagentoOrderId());

            if ($shipmentsCollection->getSize() === 0) {

                $order->getChildObject()->updateShippingStatus(array()) ? $hasSucceeded = true
                                                                        : $hasFailed = true;
                continue;
            }

            foreach ($shipmentsCollection->getItems() as $shipment) {
                /** @var Mage_Sales_Model_Order_Shipment $shipment */
                if (!$shipment->getId()) {
                    continue;
                }

                /** @var Ess_M2ePro_Model_Order_Shipment_Handler $handler */
                $handler = Ess_M2ePro_Model_Order_Shipment_Handler::factory($order->getComponentMode());
                $result  = $handler->handle($order, $shipment);

                $result == Ess_M2ePro_Model_Order_Shipment_Handler::HANDLE_RESULT_SUCCEEDED ? $hasSucceeded = true
                                                                                            : $hasFailed = true;
            }
        }

        if (!$hasFailed && $hasSucceeded) {

            $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__('Updating Walmart Order(s) Status to Shipped in Progress...')
            );

        } elseif ($hasFailed && !$hasSucceeded) {

            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('Walmart Order(s) can not be updated for Shipped Status.')
            );

        } elseif ($hasFailed && $hasSucceeded) {

            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('Some of Walmart Order(s) can not be updated for Shipped Status.')
            );
        }

        $this->_redirectUrl($this->_getRefererUrl());
    }

    //########################################

    public function goToWalmartAction()
    {
        $magentoOrderId = $this->getRequest()->getParam('magento_order_id');

        /** @var $order Ess_M2ePro_Model_Order */
        $order = Mage::helper('M2ePro/Component_Walmart')->getModel('Order')->load($magentoOrderId, 'magento_order_id');

        if (is_null($order->getId())) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Order does not exist.'));
            return $this->_redirect('*/adminhtml_walmart_order/index');
        }

        $url = Mage::helper('M2ePro/Component_Walmart')->getOrderUrl(
            $order->getChildObject()->getWalmartOrderId(), $order->getMarketplaceId()
        );

        return $this->_redirectUrl($url);
    }

    //########################################
}