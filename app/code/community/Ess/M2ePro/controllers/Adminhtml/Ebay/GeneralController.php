<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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
        $this->getResponse()->setBody(json_encode($marketplaceObj->isStatusEnabled()));
    }

    //########################################
}