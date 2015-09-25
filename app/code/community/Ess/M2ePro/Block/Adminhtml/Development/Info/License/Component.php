<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_License_Component extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/development/info/license/component.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        /** @var Ess_M2ePro_Helper_Module_License $licenseHelper */
        $licenseHelper = Mage::helper('M2ePro/Module_License');

        $component = $this->getComponent();

        if ($licenseHelper->isFreeMode($component)) {
            $licenseModeText = 'Free';
        } elseif ($licenseHelper->isLiveMode($component)) {
            $licenseModeText = 'Live';
        } elseif ($licenseHelper->isTrialMode($component)) {
            $licenseModeText = 'Trial';
        } else {
            $licenseModeText = 'Not Activated';
        }

        $this->licenseMode = $licenseModeText . Mage::helper('M2ePro')->__(' License');
        $this->licenseExpirationDate = $licenseHelper->getTextExpirationDate($component);

        return parent::_beforeToHtml();
    }

    // ########################################
}