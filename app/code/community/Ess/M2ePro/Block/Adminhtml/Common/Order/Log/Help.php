<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Order_Log_Help extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('commonOrderLogHelp');
        //------------------------------

        $this->setTemplate('M2ePro/common/order/log/help.phtml');
    }

    // ####################################
}