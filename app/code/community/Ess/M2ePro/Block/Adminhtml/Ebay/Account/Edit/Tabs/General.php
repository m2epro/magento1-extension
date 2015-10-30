<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountEditTabsGeneral');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/account/tabs/general.phtml');
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Get Token'),
            'onclick' => 'EbayAccountHandlerObj.get_token();',
            'class'   => 'get_token_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('get_token_button',$buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}