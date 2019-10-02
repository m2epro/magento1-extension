<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Order_Note extends Ess_M2ePro_Model_Abstract
{
    /** @var Ess_M2ePro_Model_Order */
    protected $_order = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Note');
    }

    //########################################

    public function getNote()
    {
        return $this->getData('note');
    }

    public function getOrderId()
    {
        return $this->getData('order_id');
    }

    //########################################

    protected function _afterDelete()
    {
        $component = $this->getOrder()->getComponentMode();

        if ($component == Ess_M2ePro_Helper_Component_Ebay::NICK) {
            $component = Mage::helper('M2ePro/View_Ebay')->getTitle();
        } else {
            $component = ucfirst($component);
        }

        $comment = Mage::helper('M2ePro')->__(
            'Custom Note for the corresponding %component% order was deleted.', $component
        );
        $this->updateMagentoOrderComments($comment);

        return parent::_afterDelete();
    }

    //########################################

    protected function _afterSave()
    {
        $component = $this->getOrder()->getComponentMode();

        if ($component == Ess_M2ePro_Helper_Component_Ebay::NICK) {
            $component = Mage::helper('M2ePro/View_Ebay')->getTitle();
        } else {
            $component = ucfirst($component);
        }

        $comment = Mage::helper('M2ePro')->__(
            'Custom Note was added to the corresponding %component% order: %text%.',
            $component,
            $this->getData('note')
        );

        if ($this->getOrigData('id') !== null) {
            $comment = Mage::helper('M2ePro')->__(
                'Custom Note for the corresponding %component% order was updated: %text%.',
                $component,
                $this->getData('note')
            );
        }

        $this->updateMagentoOrderComments($comment);

        return parent::_afterDelete();
    }

    //########################################

    protected function updateMagentoOrderComments($comment)
    {
        $magentoOrderModel = $this->getOrder()->getMagentoOrder();

        if ($magentoOrderModel !== null) {
            /** @var Ess_M2ePro_Model_Magento_Order_Updater $orderUpdater */
            $orderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');

            $orderUpdater->setMagentoOrder($magentoOrderModel);
            $orderUpdater->updateComments($comment);
            $orderUpdater->finishUpdate();
        }
    }

    //########################################

    public function getOrder()
    {
        if ($this->_order === null) {
            $this->_order = Mage::helper('M2ePro/Component')->getUnknownObject('Order', $this->getOrderId());
        }

        return $this->_order;
    }

    //########################################
}