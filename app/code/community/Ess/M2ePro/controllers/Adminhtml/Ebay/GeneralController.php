<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_GeneralController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_SimpleController
{
    //########################################

    public function isMarketplaceEnabledAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $marketplaceObj = Mage::helper('M2ePro/Component')->getUnknownObject('Marketplace',(int)$marketplaceId);

        $this->loadLayout();
        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($marketplaceObj->isStatusEnabled()));
    }

    //########################################
}