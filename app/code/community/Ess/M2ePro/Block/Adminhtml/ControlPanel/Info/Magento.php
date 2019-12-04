<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Info_Magento extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelAboutMagento');
        // ---------------------------------------

        $this->setTemplate('M2ePro/controlPanel/info/magento.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->platformMode = Mage::helper('M2ePro')->__(ucwords(Mage::helper('M2ePro/Magento')->getEditionName()));
        $this->platformVersion = Mage::helper('M2ePro/Magento')->getVersion();
        $this->platformIsSecretKey = Mage::helper('M2ePro/Magento')->isSecretKeyToUrl();

        return parent::_beforeToHtml();
    }

    //########################################
}
