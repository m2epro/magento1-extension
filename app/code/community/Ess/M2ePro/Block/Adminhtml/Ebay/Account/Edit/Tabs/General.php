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
        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Get Token'),
            'onclick' => 'EbayAccountObj.get_token();',
            'class'   => 'get_token_button',
            'id'      => 'grant_access'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('get_token_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        if ($this->isSellApiMode()) {
            $data = array(
                'label'   => Mage::helper('M2ePro')->__('Get Token'),
                'onclick' => 'EbayAccountObj.get_sell_api_token();',
                'class'   => 'get_sell_token_button'
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('get_sell_api_token_button', $buttonBlock);
        }

        // ---------------------------------------

        $confirm = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_dialog_confirm');
        $this->setChild('confirm_popup', $confirm);

        return parent::_beforeToHtml();
    }

    //########################################

    public function isSellApiMode()
    {
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if (empty($account) || !$account->getId()) {
            return $this->getRequest()->getParam('sell_api', false);
        }

        $sellApiTokenSession = $account->getChildObject()->getSellApiTokenSession();

        return $this->getRequest()->getParam('sell_api', false) || !empty($sellApiTokenSession);
    }

    //########################################
}
