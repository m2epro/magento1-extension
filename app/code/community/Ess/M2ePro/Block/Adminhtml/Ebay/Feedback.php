<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Feedback extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayFeedback');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_feedback';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $accountTitle = '';
        $accountId = $this->getRequest()->getParam('account');
        if (!is_null($accountId)) {
            $accountTitle = Mage::getModel('M2ePro/Account')->load((int)$accountId)->getTitle();
        }

        $this->_headerText = Mage::helper('M2ePro')->__('Feedback for account "%account_title%"', $accountTitle);
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('goto_orders', array(
            'label'     => Mage::helper('M2ePro')->__('Orders'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_ebay_order/index').'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('goto_accounts', array(
            'label'     => Mage::helper('M2ePro')->__('Accounts'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_ebay_account/index').'\')',
            'class'     => 'button_link'
        ));
        // ---------------------------------------
    }

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_feedback_help');
        $formBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_feedback_form');

        return $helpBlock->toHtml() . $formBlock->toHtml() . parent::getGridHtml();
    }

    //########################################
}