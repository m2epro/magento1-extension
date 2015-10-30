<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_Buy_OrderController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Rakuten.com Orders'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Order/Debug.js')
             ->addJs('M2ePro/OrderHandler.js')
             ->addJs('M2ePro/Order/Edit/ItemHandler.js')
             ->addJs('M2ePro/Order/Edit/ShippingAddressHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/orders');
    }

    //########################################

    public function indexAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->_redirect('*/adminhtml_common_order/index');
        }

        /** @var $block Ess_M2ePro_Block_Adminhtml_Common_Order */
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_order');
        $block->enableBuyTab();

        $this->getResponse()->setBody($block->getBuyTabHtml());
    }

    public function gridAction()
    {
        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_buy_order_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Buy')->getObject('Order', (int)$id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $order);

        $this->_initAction();

        $this->setComponentPageHelpLink('Manage+Order+Details', Ess_M2ePro_Helper_Component_Buy::NICK);

        $this->_initPopUp();
        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_order_view'))
             ->renderLayout();
    }

    //########################################

    public function orderItemGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Buy')->getObject('Order', (int)$id);

        if (!$id || !$order->getId()) {
            return;
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $order);

        $response = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_common_buy_order_view_item')
            ->toHtml();
        $this->getResponse()->setBody($response);
    }

    //########################################

    public function createMagentoOrderAction()
    {
        $id = $this->getRequest()->getParam('id');
        $force = $this->getRequest()->getParam('force');

        /** @var $order Ess_M2ePro_Model_Order */
        $order = Mage::helper('M2ePro/Component_Buy')->getObject('Order', (int)$id);

    // M2ePro_TRANSLATIONS
    // Magento Order is already created for this Rakuten.com Order. Press Create Order Button to create new one.
        if (!is_null($order->getMagentoOrderId()) && $force != 'yes') {
            $message = 'Magento Order is already created for this Rakuten.com Order. ' .
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
                Mage::getSingleton('M2ePro/Log_Abstract')->decodeDescription($e->getMessage())
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

        $this->_redirect('*/*/view', array('id' => $id));
    }

    //########################################

    public function editShippingAddressAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Buy')->getObject('Order', (int)$id);

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $order);

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_order_edit_shippingAddress'))
             ->renderLayout();
    }

    public function saveShippingAddressAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/adminhtml_common_order/index');
        }

        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::helper('M2ePro/Component_Buy')->getObject('Order', (int)$id);

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

        $order->addData($data);

        $data = json_decode($order->getData('shipping_address'), true);
        $keys = array(
            'state',
            'city',
            'postal_code',
            'recipient_name',
            'street'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $order->setData('shipping_address', json_encode($data));

        $data = json_decode($order->getData('billing_address'), true);
        $keys = array(
            'phone',
            'company'
        );

        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = $post[$key];
            }
        }

        $order->setData('billing_address', json_encode($data));
        $order->save();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Order address has been updated.'));

        $this->_redirect('*/adminhtml_common_buy_order/view', array('id' => $order->getId()));
    }

    //########################################
}