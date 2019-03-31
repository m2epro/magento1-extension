<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
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
        $walmartMode = (int)$this->getRequest()->getParam('component_walmart_mode');

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/component/ebay/', 'mode',
            $ebayMode
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/component/amazon/', 'mode',
            $amazonMode
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/component/walmart/', 'mode',
            $walmartMode
        );

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('The global Settings have been successfully saved.')
        );

        $this->_redirectUrl($this->_getRefererUrl());
    }

    //########################################
}