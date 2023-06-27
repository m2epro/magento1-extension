<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_OrderController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Sales'))
             ->_title(Mage::helper('M2ePro')->__('Orders'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Plugin/ProgressBar.js')
             ->addCss('M2ePro/css/Plugin/ProgressBar.css')
             ->addJs('M2ePro/Order/Debug.js')
             ->addJs('M2ePro/Order.js')
             ->addJs('M2ePro/Order/Edit/Item.js')
             ->addJs('M2ePro/Order/Edit/ShippingAddress.js')
             ->addJs('M2ePro/Ebay/Order/MigrationToV611.js')
             ->addJs('M2ePro/Grid.js')
             ->addJs('M2ePro/Order/Note.js')
             ->addJs('M2ePro/Plugin/ActionColumn.js');

        $this->setPageHelpLink(null, null, "sales-and-orders");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/orders'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction();
        $this->_initPopUp();
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_order'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_order_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', (int)$id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $order);

        $this->_initAction();

        $this->setPageHelpLink(null, null, "sales-and-orders");

        $this->_initPopUp();

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_order_view'))
             ->renderLayout();
    }

    //########################################

    public function orderItemGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', (int)$id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $order);

        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_order_view_item')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function editShippingAddressAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', (int)$id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $order);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_order_edit_shippingAddress'))
             ->renderLayout();
    }

    public function saveShippingAddressAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/adminhtml_ebay_order/index');
        }

        $id = $this->getRequest()->getParam('order_id');

        /** @var Ess_M2ePro_Model_Order $order */
        $order = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', (int)$id);

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
            'recipient_name',
            'street',
            'city',
            'country_code',
            'state',
            'postal_code',
            'phone'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        if (isset($data['street']) && is_array($data['street'])) {
            $data['street'] = array_filter($data['street']);
        }

        $shippingDetails = $order->getChildObject()->getShippingDetails();
        $shippingDetails['address'] = $data;

        $order->setData('shipping_details', Mage::helper('M2ePro')->jsonEncode($shippingDetails));
        $order->save();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Order address has been updated.'));

        $this->_redirect('*/adminhtml_ebay_order/view', array('id' => $order->getId()));
    }

    //########################################

    public function updateShippingStatusAction()
    {
        $ids = $this->getRequestIds();

        if (empty($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Order(s).'));
            $this->_redirect('*/*/index');
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Order_Collection $ordersCollection */
        $ordersCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order')
            ->addFieldToFilter('id', array('in' => $ids));
        /** @var Ess_M2ePro_Model_Order_Shipment_Handler $handler */
        $handler = Mage::getModel("M2ePro/Ebay_Order_Shipment_Handler");

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

                $result  = $handler->handle($order, $shipment);

                $result == Ess_M2ePro_Model_Order_Shipment_Handler::HANDLE_RESULT_SUCCEEDED ? $hasSucceeded = true
                                                                                            : $hasFailed = true;
            }
        }

        if (!$hasFailed && $hasSucceeded) {
            $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__('Updating eBay Order(s) Status to Shipped in Progress...')
            );
        } elseif ($hasFailed && !$hasSucceeded) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('eBay Order(s) can not be updated for Shipped Status.')
            );
        } elseif ($hasFailed && $hasSucceeded) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('Some of eBay Order(s) can not be updated for Shipped Status.')
            );
        }

        $this->_redirectUrl($this->_getRefererUrl());
    }

    public function updatePaymentStatusAction()
    {
        $ids = $this->getRequestIds();

        if (empty($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select Order(s).'));
            return $this->_redirect('*/*/index');
        }

        /** @var Ess_M2ePro_Model_Resource_Order_Collection $ordersCollection */
        $ordersCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order')
            ->addFieldToFilter('id', array('in' => $ids));

        $hasFailed = false;
        $hasSucceeded = false;

        foreach ($ordersCollection->getItems() as $order) {
            /** @var Ess_M2ePro_Model_Order $order */

            $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);

            $order->getChildObject()->updatePaymentStatus() ? $hasSucceeded = true
                                                            : $hasFailed = true;
        }

        if (!$hasFailed && $hasSucceeded) {
            $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__('Updating eBay Order(s) Status to Paid in Progress...')
            );
        } elseif ($hasFailed && !$hasSucceeded) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('eBay Order(s) can not be updated for Paid Status.')
            );
        } elseif ($hasFailed && $hasSucceeded) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('Some of eBay Order(s) can not be updated for Paid Status.')
            );
        }

        return $this->_redirectUrl($this->_getRefererUrl());
    }

    public function createMagentoOrderAction()
    {
        $ids = $this->getRequestIds();
        $isForce = (bool)$this->getRequest()->getParam('force');
        $warnings = 0;
        $errors = 0;

        foreach ($ids as $id) {

            /** @var $order Ess_M2ePro_Model_Order */
            $order = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', (int)$id);
            $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);

            if ($order->getMagentoOrderId() !== null && !$isForce) {
                $warnings++;
                continue;
            }

            // Create magento order
            // ---------------------------------------
            try {
                $order->createMagentoOrder($isForce);
            } catch (Exception $e) {
                $errors++;
            }

            // ---------------------------------------

            if ($order->getChildObject()->canCreatePaymentTransaction()) {
                $order->getChildObject()->createPaymentTransactions();
            }

            if ($order->getChildObject()->canCreateInvoice()) {
                $order->createInvoice();
            }

            $order->createShipment();

            if ($order->getChildObject()->canCreateTracks()) {
                $order->getChildObject()->createTracks();
            }

            // ---------------------------------------
            $order->updateMagentoOrderStatus();
            // ---------------------------------------
        }

        if (!$errors && !$warnings) {
            $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Magento order(s) were created.'));
        }

        if ($errors) {
            $this->getSession()->addError(
                Mage::helper('M2ePro')->__(
                    '%count% Magento order(s) were not created. Please <a target="_blank" href="%url%">view Log</a>
                for the details.',
                    $errors, $this->getUrl('*/adminhtml_ebay_log/order')
                )
            );
        }

        if ($warnings) {
            $this->_getSession()->addWarning(
                Mage::helper('M2ePro')->__(
                    '%count% Magento order(s) are already created for the selected eBay order(s).', $warnings
                )
            );
        }

        if (count($ids) == 1) {
            $this->_redirect('*/*/view', array('id' => $ids[0]));
        } else {
            $this->_redirect('*/*/index');
        }
    }

    //########################################

    public function goToPaypalAction()
    {
        $transactionId = $this->getRequest()->getParam('transaction_id');

        if (!$transactionId) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Transaction ID should be defined.'));
            return $this->_redirect('*/adminhtml_ebay_order/index');
        }

        /** @var $transaction Ess_M2ePro_Model_Ebay_Order_ExternalTransaction */
        $transaction = Mage::getModel('M2ePro/Ebay_Order_ExternalTransaction')->load($transactionId, 'transaction_id');

        if ($transaction->getId() === null) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('eBay Order Transaction does not exist.'));
            return $this->_redirect('*/adminhtml_ebay_order/index');
        }

        if (!$transaction->isPaypal()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('This is not a PayPal Transaction.'));
            return $this->_redirect('*/adminhtml_ebay_order/index');
        }

        return $this->_redirectUrl($transaction->getPaypalUrl());
    }

    //########################################

    public function migrateOrdersPackToV611Action()
    {
        $ordersCount = (int)$this->getRequest()->getParam('orders_count');
        if ($ordersCount <= 0) {
            return;
        }

        /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion611_OrdersData $migrationModel */
        $migrationModel = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion611_OrdersData');
        $migrationModel->setMaxOrdersCount($ordersCount);
        $migrationModel->migrate();
    }

    //########################################
}
