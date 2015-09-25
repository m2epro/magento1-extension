<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Support_Form extends Mage_Adminhtml_Block_Widget_Form
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('supportGeneralForm');
        //------------------------------

        // Set template
        //------------------------------
        $this->setTemplate('M2ePro/support.phtml');
        //------------------------------
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $this->isFromError = $this->getRequest()->getParam('error') === 'true';
        //-------------------------------

        //-------------------------------
        $cronInfoBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_inspection_cron',
            '',
            array('is_support_mode' => true)
        );
        $this->setChild('cron_info', $cronInfoBlock);
        //-------------------------------

        //-------------------------------
        $systemRequirementsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_inspection_requirements',
            '',
            array('is_support_mode' => true)
        );
        $this->setChild('system_requirements', $systemRequirementsBlock);
        //-------------------------------

        //-------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Search'),
            'onclick' => 'SupportHandlerObj.searchUserVoiceData();',
            'id'      => 'send_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('user_voice_search',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Contact Support'),
            'onclick' => 'SupportHandlerObj.forceShowContactSupportForm();',
            'id'      => 'force_show_contact_support_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('force_show_contact_support_button',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $tabsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_support_tabs', '',
            array('is_from_error' => $this->isFromError)
        );
        $this->setChild('tabs', $tabsBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}