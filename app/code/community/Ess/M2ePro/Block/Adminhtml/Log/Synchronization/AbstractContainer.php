<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Log_Synchronization_AbstractContainer extends
    Mage_Adminhtml_Block_Widget_Grid_Container
{
    //#######################################

    abstract protected function getComponentMode();

    //#######################################

    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_' . $this->getComponentMode() . '_log_synchronization';

        $this->setId(ucfirst($this->getComponentMode()) . 'LogSynchronization');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->setTemplate('M2ePro/widget/grid/container/only_content.phtml');
    }

    //########################################
}
