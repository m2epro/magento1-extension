<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Account extends Ess_M2ePro_Block_Adminhtml_Common_Component_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('account');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_account';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $this->_headerText = '';
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
        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Account'),
            'onclick'   => $this->getAddButtonOnClickAction(),
            'class'     => 'add add-button-drop-down'
        ));
        // ---------------------------------------
    }

    //########################################

    protected function getAmazonNewUrl()
    {
        return $this->getUrl('*/adminhtml_common_amazon_account/new');
    }

    protected function getBuyNewUrl()
    {
        return $this->getUrl('*/adminhtml_common_buy_account/new');
    }

    //########################################
}