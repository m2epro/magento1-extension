<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationEbay_Installation_ModeConfirmation_Content
    extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardInstallationModeConfirmation');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/installationEbay/installation/mode_confirmation.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Continue'),
                'onclick' => '',
                'id' => 'continue_button'
            ) );
        $this->setChild('continue_button',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################

    public function getMode()
    {
        $account = Mage::helper('M2ePro/Component_Ebay')
                ->getCollection('Account')
                ->getLastItem();

        if ($account->getMode() == Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX) {
            return Ess_M2ePro_Helper_View_Ebay::MODE_ADVANCED;
        }

        $accountInfo = json_decode($account->getData('ebay_info'),true);

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $registrationDate = isset($accountInfo['RegistrationDate']) ? $accountInfo['RegistrationDate'] : false;

        // if registration date is less than 3 months
        if (!$registrationDate ||
            ($currentTimeStamp - strtotime($registrationDate)) < 60*60*24*30*3) {
            return Ess_M2ePro_Helper_View_Ebay::MODE_SIMPLE;
        }

        $feedbackScore = isset($accountInfo['FeedbackScore']) ? $accountInfo['FeedbackScore'] : 0;

        if ($feedbackScore < 100) {
            return Ess_M2ePro_Helper_View_Ebay::MODE_SIMPLE;
        }

        return Ess_M2ePro_Helper_View_Ebay::MODE_ADVANCED;
    }

    // ########################################
}