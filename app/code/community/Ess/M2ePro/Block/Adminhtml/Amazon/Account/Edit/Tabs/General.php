<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Account_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonAccountEditTabsGeneral');
        $this->setTemplate('M2ePro/amazon/account/tabs/general.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $marketplaces = Mage::helper('M2ePro/Component_Amazon')->getMarketplacesAvailableForApiCreation();
        $marketplaces = $marketplaces->toArray();
        $this->marketplaces = $marketplaces['items'];

        $confirm = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_dialog_confirm');
        $this->setChild('confirm_popup', $confirm);

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Check Token Validity'),
            'onclick' => 'AmazonAccountObj.check_click()',
            'class'   => 'check M2ePro_check_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('check_token_validity', $buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}
