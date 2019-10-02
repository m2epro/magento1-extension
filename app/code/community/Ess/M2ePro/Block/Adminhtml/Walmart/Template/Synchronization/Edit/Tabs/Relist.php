<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Template_Synchronization_Edit_Tabs_Relist
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartTemplateSynchronizationEditTabsRelist');
        // ---------------------------------------

        $this->setTemplate('M2ePro/walmart/template/synchronization/relist.phtml');
    }

    //########################################
}
