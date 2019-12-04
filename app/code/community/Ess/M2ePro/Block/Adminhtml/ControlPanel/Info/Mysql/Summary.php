<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Info_Mysql_Summary extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelAboutMysqlSummary');
        // ---------------------------------------

        $this->setTemplate('M2ePro/controlPanel/info/mysql/summary.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->mySqlTotal = Mage::helper('M2ePro/Client')->getMysqlTotals();

        return parent::_beforeToHtml();
    }

    //########################################
}
