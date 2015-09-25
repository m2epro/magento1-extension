<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Inspection extends Mage_Adminhtml_Block_Widget_Form
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentInspection');
        //------------------------------

        $this->setTemplate('M2ePro/development/tabs/inspection.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //----------------------------
        $this->setChild('requirements', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_inspection_requirements'
        ));
        $this->setChild('cron', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_inspection_cron'
        ));
        //----------------------------

        //----------------------------
        $this->setChild('caches', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_inspection_caches'
        ));
        $this->setChild('conflicted_modules', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_inspection_conflictedModules'
        ));
        $this->setChild('magento', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_inspection_magento'
        ));
        $this->setChild('database_broken', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_inspection_databaseBrokenTables'
        ));
        $this->setChild('installation', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_inspection_installation'
        ));
        $this->setChild('other_issues', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_inspection_otherIssues'
        ));
        //----------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}