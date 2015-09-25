<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_About extends Mage_Adminhtml_Block_Widget_Form
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentAbout');
        //------------------------------

        $this->setTemplate('M2ePro/development/tabs/about.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->components = Mage::helper('M2ePro/Component')->getComponentsTitles();

        //-- Left Column
        //----------------------------
        $this->setChild('module', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_module'
        ));
        $this->setChild('license_information', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_license_information'
        ));
        $this->setChild('location', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_location'
        ));

        foreach ($this->components as $component => $title) {
            $this->setChild('license_component_' . $component, $this->getLayout()->createBlock(
                'M2ePro/adminhtml_development_info_license_component',
                '',
                array('component' => $component)
            ));
        }
        //----------------------------

        //-- Right Column
        //----------------------------
        $this->setChild('magento', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_magento')
        );
        $this->setChild('system', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_system')
        );
        $this->setChild('php', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_php')
        );
        $this->setChild('mysql_info', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_mysql_info')
        );
        $this->setChild('mysql_summary', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_mysql_summary')
        );
        //----------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}