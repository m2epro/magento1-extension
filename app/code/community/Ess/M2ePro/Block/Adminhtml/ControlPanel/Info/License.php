<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Info_License extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('controlPanelLicense');
        $this->setTemplate('M2ePro/controlPanel/info/license.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        /** @var Ess_M2ePro_Helper_Module_License $licenseHelper */
        $licenseHelper = Mage::helper('M2ePro/Module_License');

        $this->licenseData = array(
            'key'    => Mage::helper('M2ePro')->escapeHtml($licenseHelper->getKey()),
            'domain' => Mage::helper('M2ePro')->escapeHtml($licenseHelper->getDomain()),
            'ip'     => Mage::helper('M2ePro')->escapeHtml($licenseHelper->getIp()),
            'valid'  => array(
                'domain' => $licenseHelper->isValidDomain(),
                'ip'     => $licenseHelper->isValidIp()
            )
        );

        $this->locationData = array(
            'domain'    => Mage::helper('M2ePro/Client')->getDomain(),
            'ip'        => Mage::helper('M2ePro/Client')->getIp(),
            'directory' => Mage::helper('M2ePro/Client')->getBaseDirectory()
        );

        return parent::_beforeToHtml();
    }

    //########################################
}
