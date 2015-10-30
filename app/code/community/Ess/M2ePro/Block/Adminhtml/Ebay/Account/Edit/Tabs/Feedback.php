<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Edit_Tabs_Feedback extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountEditTabsFeedback');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/account/tabs/feedback.phtml');
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Add Template'),
            'onclick' => 'EbayAccountHandlerObj.feedbacksOpenAddForm();',
            'class'   => 'open_add_form'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('open_add_form',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Save'),
            'onclick' => 'EbayAccountHandlerObj.feedbacksAddAction();',
            'class'   => 'add_action'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('add_action',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Save'),
            'onclick' => 'EbayAccountHandlerObj.feedbacksEditAction();',
            'class'   => 'edit_action'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('edit_action',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $this->setChild('feedback_template_grid',
                        $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_account_edit_tabs_feedback_grid'));
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}