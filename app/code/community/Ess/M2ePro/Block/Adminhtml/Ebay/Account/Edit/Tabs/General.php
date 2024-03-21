<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
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
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        $url = $this->getUrl(
            '*/adminhtml_ebay_account/beforeGetSellApiToken',
            array(
                'id' => $account->getId(),
                'mode' => $account->getChildObject()->getMode()
            )
        );

        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Get Token'),
            'onclick' => 'setLocation(\'' . $url . '\');',
            'class'   => 'get_sell_token_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('get_sell_api_token_button', $buttonBlock);


        $confirm = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_dialog_confirm');
        $this->setChild('confirm_popup', $confirm);

        return parent::_beforeToHtml();
    }
}
