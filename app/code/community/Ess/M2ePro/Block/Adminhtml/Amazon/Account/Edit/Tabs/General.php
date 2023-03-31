<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Account_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    /** @var Ess_M2ePro_Model_Marketplace */
    protected $marketplace;

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonAccountEditTabsGeneral');
        $this->setTemplate('M2ePro/amazon/account/tabs/general.phtml');
    }

    // ----------------------------------------

    protected function _beforeToHtml()
    {
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Data_Global')->getValue('model_account');
        $this->marketplace = Mage::helper('M2ePro/Component_Amazon')
            ->getModel('Marketplace')
            ->load($account->getChildObject()->getMarketplaceId());

        $confirm = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_dialog_confirm');
        $this->setChild('confirm_popup', $confirm);

        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Check Token Validity'),
            'onclick' => 'AmazonAccountObj.check_click()',
            'class'   => 'check M2ePro_check_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('check_token_validity', $buttonBlock);

        $marketplaceId = $this->marketplace->getId();
        $data = array(
            'label' => Mage::helper('M2ePro')->__('Update Access Data'),
            'onclick' => "AmazonAccountObj.get_token($marketplaceId);",
            'class' => 'check M2ePro_check_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('update_access_data', $buttonBlock);

        return parent::_beforeToHtml();
    }
}
