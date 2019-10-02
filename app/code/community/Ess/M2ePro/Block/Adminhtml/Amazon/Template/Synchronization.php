<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Template_Synchronization extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('templateSynchronization');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_template_synchronization';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Synchronization Policies');
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/adminhtml_amazon_listing/index');
        $this->_addButton(
            'goto_listings', array(
            'label' => Mage::helper('M2ePro')->__('Listings'),
            'onclick' => 'setLocation(\'' . $url . '\')',
            'class' => 'button_link'
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/adminhtml_amazon_synchronization/index');
        $this->_addButton(
            'goto_synchronization', array(
            'label' => Mage::helper('M2ePro')->__('Synchronization Settings'),
            'onclick' => 'setLocation(\'' . $url . '\')',
            'class' => 'button_link'
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton(
            'add', array(
            'label' => Mage::helper('M2ePro')->__('Add Synchronization Policy'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/adminhtml_amazon_template_synchronization/new') . '\');',
            'class' => 'add add-button-drop-down'
            )
        );
        // ---------------------------------------
    }

    //########################################

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_template_synchronization_help');
        return $helpBlock->toHtml() . parent::getGridHtml();
    }

    //########################################
}
