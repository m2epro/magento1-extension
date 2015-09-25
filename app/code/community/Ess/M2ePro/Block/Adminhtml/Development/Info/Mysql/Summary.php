<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_Mysql_Summary extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentAboutMysqlSummary');
        //------------------------------

        $this->setTemplate('M2ePro/development/info/mysql/summary.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->mySqlTotal = Mage::helper('M2ePro/Client')->getMysqlTotals();

        return parent::_beforeToHtml();
    }

    // ########################################
}