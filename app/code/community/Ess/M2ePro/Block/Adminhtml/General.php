<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_General extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('generalHtml');
        //------------------------------

        $this->setTemplate('M2ePro/general.phtml');
    }

    protected function _beforeToHtml()
    {
        // Set data for form
        //----------------------------
        $this->block_notices_show = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/', 'show_block_notices'
        );
        //----------------------------

        $this->initM2eProInfo();

        return parent::_beforeToHtml();
    }

    // ########################################

    protected function initM2eProInfo()
    {
        $this->m2epro_info = array(
            'platform' => array(
                'name' => Mage::helper('M2ePro/Magento')->getName().' ('.
                          Mage::helper('M2ePro/Magento')->getEditionName().')',
                'version' => Mage::helper('M2ePro/Magento')->getVersion(),
                'revision' => Mage::helper('M2ePro/Magento')->getRevision(),
            ),
            'module' => array(
                'name' => Mage::helper('M2ePro/Module')->getName(),
                'version' => Mage::helper('M2ePro/Module')->getVersion(),
                'revision' => Mage::helper('M2ePro/Module')->getRevision()
            ),
            'location' => array(
                'domain' => Mage::helper('M2ePro/Client')->getDomain(),
                'ip' => Mage::helper('M2ePro/Client')->getIp(),
                'directory' => Mage::helper('M2ePro/Client')->getBaseDirectory()
            ),
            'locale' => Mage::helper('M2ePro/Magento')->getLocale()
        );
    }

    // ########################################
}