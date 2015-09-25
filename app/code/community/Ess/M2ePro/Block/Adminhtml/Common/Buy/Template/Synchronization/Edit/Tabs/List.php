<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Template_Synchronization_Edit_Tabs_List extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyTemplateSynchronizationEditTabsList');
        //------------------------------

        $this->setTemplate('M2ePro/common/buy/template/synchronization/list.phtml');
    }

    // ####################################
}