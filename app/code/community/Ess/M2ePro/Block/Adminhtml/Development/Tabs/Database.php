<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Database extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentDatabase');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_development_tabs_database';
        //------------------------------

        $this->setTemplate('M2ePro/widget/grid/container/only_content.phtml');
    }

    // ########################################
}