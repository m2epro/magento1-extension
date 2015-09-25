<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Synchronization_Form extends Mage_Adminhtml_Block_Widget_Form
{
    private $component = Ess_M2ePro_Helper_Component_Amazon::NICK;

    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonSynchronizationForm');
        $this->setContainerId('magento_block_amazon_synchronization');
        $this->setTemplate('M2ePro/common/amazon/synchronization.phtml');
        //------------------------------
    }

    protected function _beforeToHtml()
    {
        //----------------------------
        $this->templatesMode = Mage::helper('M2ePro/Module')->getSynchronizationConfig()
            ->getGroupValue('/amazon/templates/', 'mode');
        //----------------------------

        //----------------------------
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $this->reviseAllInProcessingState = !is_null(
            Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
                '/amazon/templates/revise/total/', 'last_listing_product_id'
            )
        );

        $this->reviseAllStartDate = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/amazon/templates/revise/total/', 'start_date'
        );
        $this->reviseAllStartDate && $this->reviseAllStartDate = Mage::app()->getLocale()
            ->date(strtotime($this->reviseAllStartDate))
            ->toString($format);

        $this->reviseAllEndDate = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/amazon/templates/revise/total/', 'end_date'
        );
        $this->reviseAllEndDate && $this->reviseAllEndDate = Mage::app()->getLocale()
            ->date(strtotime($this->reviseAllEndDate))
            ->toString($format);
        //----------------------------

        //----------------------------
        $component = Ess_M2ePro_Helper_Component_Amazon::NICK;
        $data = array(
            'class'   => 'ok_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => "Windows.getFocusedWindow().close(); SynchronizationHandlerObj.runReviseAll('{$component}');",
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('revise_all_confirm_popup_ok_button', $buttonBlock);
        //------------------------------

        //-------------------------------
        $this->inspectorMode = (int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/defaults/inspector/','mode'
        );
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ####################################

    public function isShowReviseAll()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/synchronization/revise_total/','show'
        );
    }

    // ####################################
}