<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Template_SellingFormat
    extends Ess_M2ePro_Block_Adminhtml_Common_Component_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('templateSellingFormat');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_template_sellingFormat';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Selling Format Policies');
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
        $url = $this->getUrl('*/adminhtml_common_listing/index');
        $this->_addButton('goto_listings', array(
            'label'     => Mage::helper('M2ePro')->__('Listings'),
            'onclick'   => 'setLocation(\'' . $url .'\')',
            'class'     => 'button_link'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Selling Format Policy'),
            'onclick'   => $this->getAddButtonOnClickAction(),
            'class'     => 'add add-button-drop-down'
        ));
        // ---------------------------------------
    }

    //########################################

    protected function getAmazonNewUrl()
    {
        return $this->getUrl('*/adminhtml_common_amazon_template_sellingFormat/new');
    }

    protected function getBuyNewUrl()
    {
        return $this->getUrl('*/adminhtml_common_buy_template_sellingFormat/new');
    }

    //########################################

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_template_sellingFormat_help');
        return $helpBlock->toHtml() . parent::getGridHtml();
    }

    //########################################
}