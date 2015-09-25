<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_GeneralController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_SimpleController
{
    //#############################################

    public function isMarketplaceEnabledAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $marketplaceObj = Mage::helper('M2ePro/Component')->getUnknownObject('Marketplace',(int)$marketplaceId);

        $this->loadLayout();
        $this->getResponse()->setBody(json_encode($marketplaceObj->isStatusEnabled()));
    }

    //#############################################
}