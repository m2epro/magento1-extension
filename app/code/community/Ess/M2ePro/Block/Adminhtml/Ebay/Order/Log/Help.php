<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Order_Log_Help extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayOrderLogHelp');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/order/log/help.phtml');
    }

    // ####################################
}