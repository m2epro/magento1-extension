<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Summary extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('developmentSummary');
        // ---------------------------------------

        $this->setTemplate('M2ePro/development/tabs/summary.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->setChild('actual_info', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_actual'
        ));

        $this->setChild('inspection', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_inspection'
        ));

        $this->setChild('database_module', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_mysql_module'
        ));

        $this->setChild('database_integration', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_info_mysql_integration'
        ));

        return parent::_beforeToHtml();
    }

    //########################################
}