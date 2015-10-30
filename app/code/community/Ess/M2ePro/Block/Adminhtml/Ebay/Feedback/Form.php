<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Feedback_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

   public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayFeedbackForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/feedback/form.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Send'),
            'onclick' => 'EbayFeedbackHandlerObj.sendFeedback();',
            'class'   => 'send_feedback'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('send_feedback',$buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}