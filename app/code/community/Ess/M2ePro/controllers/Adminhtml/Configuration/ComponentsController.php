<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Configuration_ComponentsController
    extends Ess_M2ePro_Controller_Adminhtml_Configuration_MainController
{
    //########################################

    public function saveAction()
    {
        $ebayMode = (int)$this->getRequest()->getParam('component_ebay_mode');
        $amazonMode = (int)$this->getRequest()->getParam('component_amazon_mode');
        $buyMode = (int)$this->getRequest()->getParam('component_buy_mode');

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/view/common/component/', 'default',
            $this->getRequest()->getParam('view_common_component_default', Ess_M2ePro_Helper_Component_Amazon::NICK)
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/component/ebay/', 'mode',
            $ebayMode
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/component/amazon/', 'mode',
            $amazonMode
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/component/buy/', 'mode',
            $buyMode
        );

        // Update Buy marketplace status
        // ---------------------------------------
        Mage::helper('M2ePro/Component_Buy')->getCollection('Marketplace')
            ->getFirstItem()
            ->setData('status', $buyMode)
            ->save();
        // ---------------------------------------

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('The global Settings have been successfully saved.')
        );

        $this->_redirectUrl($this->_getRefererUrl());
    }

    //########################################
}