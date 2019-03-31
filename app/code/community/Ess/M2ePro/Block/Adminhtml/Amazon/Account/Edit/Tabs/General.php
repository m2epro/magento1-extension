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

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonAccountEditTabsGeneral');
        // ---------------------------------------

        $this->setTemplate('M2ePro/amazon/account/tabs/general.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->synchronizeProcessing = false;

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data') &&
            Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()) {

            /** @var $accountObj Ess_M2ePro_Model_Account */
            $accountObj = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

            $this->synchronizeProcessing = $accountObj->isSetProcessingLock('server_synchronize');

            if (!$this->synchronizeProcessing) {
                $accountId = $accountObj->getId();

                Mage::helper('M2ePro/Data_Global')->unsetValue('temp_data');
                Mage::helper('M2ePro/Data_Global')->setValue(
                    'temp_data',
                    Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Account',$accountId)
                );
            }
        }

        $marketplaces = Mage::helper('M2ePro/Component_Amazon')->getMarketplacesAvailableForApiCreation();
        $marketplaces = $marketplaces->toArray();
        $this->marketplaces = $marketplaces['items'];

        $confirm = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_dialog_confirm');
        $this->setChild('confirm_popup',$confirm);

        return parent::_beforeToHtml();
    }

    //########################################
}