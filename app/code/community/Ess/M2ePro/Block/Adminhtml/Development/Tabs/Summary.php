<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Summary extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentSummary');
        //------------------------------

        $this->setTemplate('M2ePro/development/tabs/summary.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->setChild('actual_info', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_actual'
        ));

        $this->setChild('database_module', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_mysql_module'
        ));

        $this->setChild('database_integration', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_mysql_integration'
        ));

        return parent::_beforeToHtml();
    }

    // ########################################
}