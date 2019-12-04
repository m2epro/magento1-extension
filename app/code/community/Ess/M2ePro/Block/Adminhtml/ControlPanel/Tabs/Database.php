<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Tabs_Database extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelDatabase');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_controlPanel_tabs_database';
        // ---------------------------------------

        $this->setTemplate('M2ePro/widget/grid/container/only_content.phtml');
    }

    //########################################
}
