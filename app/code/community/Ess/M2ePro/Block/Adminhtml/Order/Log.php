<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Order_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $args = func_get_args();
        $component = $args[0]['component_mode'];

        $this->setId("{$component}OrderLog");
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_order_log';

        $this->_headerText = '';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->setTemplate('M2ePro/order/log.phtml');
    }

    //########################################

    public function getFilterBlock()
    {
        $accountFilterBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_account_switcher', '', array(
                'component_mode'  => $this->getComponentMode(),
                'controller_name' => 'adminhtml_' . $this->getComponentMode() . '_log'
            )
        );
        $accountFilterBlock->setUseConfirm(false);

        $marketplaceFilterBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_marketplace_switcher', '', array(
                'component_mode'  => $this->getComponentMode(),
                'controller_name' => 'adminhtml_' . $this->getComponentMode() . '_log'
            )
        );
        $marketplaceFilterBlock->setUseConfirm(false);

        return $accountFilterBlock->_toHtml()
            . $marketplaceFilterBlock->_toHtml();
    }

    //########################################

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getChild('grid')->setData('component_mode', $this->getComponentMode());

        return $this;
    }

    //########################################

    protected function getComponentMode()
    {
        return $this->getData('component_mode');
    }

    //########################################
}
