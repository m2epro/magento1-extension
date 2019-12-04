<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Account_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartAccountEditTabsGeneral');
        $this->setTemplate('M2ePro/walmart/account/tabs/general.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $marketplaces = Mage::helper('M2ePro/Component_Walmart')->getMarketplacesAvailableForApiCreation();
        $marketplaces = $marketplaces->toArray();
        $this->marketplaces = $marketplaces['items'];

        $confirm = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_dialog_confirm');
        $this->setChild('confirm_popup', $confirm);

        return parent::_beforeToHtml();
    }

    //########################################
}