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

    /**
     * @return Ess_M2ePro_Model_Marketplace
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getMarketplace()
    {
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        return $account->getChildObject()->getMarketplace();
    }

    protected function _beforeToHtml()
    {
        if ((int)$this->getMarketplace()->getId() === Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA) {
            $updateAccount = $this->getUrl(
                '*/adminhtml_walmart_account_canada_accountUpdate/updateCredentials',
                array(
                    'marketplace_id' => Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_CA,
                    'id' => $this->getRequest()->getParam('id'),
                    '_current' => true,
                )
            );

            $data = array(
                'label'   => Mage::helper('M2ePro')->__('Update Access Data'),
                'onclick' => "WalmartAccountObj.openAccessDataPopup('{$updateAccount}')",
            );
        } else {
            $url = $this->getUrl(
                '*/adminhtml_walmart_account_unitedStates_beforeGetToken/beforeGetToken',
                array(
                    'marketplace_id' => Ess_M2ePro_Helper_Component_Walmart::MARKETPLACE_US,
                    'id' => $this->getRequest()->getParam('id'),
                    '_current' => true,
                )
            );

            $data = array(
                'label'   => Mage::helper('M2ePro')->__('Update Access Data'),
                'onclick' => 'setLocation(\'' . $url . '\');',
            );

        }

        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('update_access_button', $buttonBlock);

        $confirm = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_dialog_confirm');
        $this->setChild('confirm_popup', $confirm);

        return parent::_beforeToHtml();
    }

    //########################################
}