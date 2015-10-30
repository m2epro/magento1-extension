<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_License_Information extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('developmentAboutLicenseInformation');
        // ---------------------------------------

        $this->setTemplate('M2ePro/development/info/license/information.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        /** @var Ess_M2ePro_Helper_Module_License $licenseHelper */
        $licenseHelper = Mage::helper('M2ePro/Module_License');

        $this->licenseKey = Mage::helper('M2ePro')->escapeHtml($licenseHelper->getKey());

        $this->licenseData = array(
            'domain' => Mage::helper('M2ePro')->escapeHtml($licenseHelper->getDomain()),
            'ip' => Mage::helper('M2ePro')->escapeHtml($licenseHelper->getIp()),
            'directory' => Mage::helper('M2ePro')->escapeHtml($licenseHelper->getDirectory()),
            'valid' => array(
                'domain' => $licenseHelper->isValidDomain(),
                'ip' => $licenseHelper->isValidIp(),
                'directory' => $licenseHelper->isValidDirectory()
            )
        );

        return parent::_beforeToHtml();
    }

    //########################################
}