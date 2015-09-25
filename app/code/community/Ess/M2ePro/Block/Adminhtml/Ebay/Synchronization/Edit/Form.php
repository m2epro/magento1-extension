<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Synchronization_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('edit_form');
        $this->setContainerId('magento_block_ebay_synchronization');
        $this->setTemplate('M2ePro/ebay/synchronization/form.phtml');
        //------------------------------
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //----------------------------
        $this->templates = Mage::helper('M2ePro/Module')->getSynchronizationConfig()
            ->getAllGroupValues('/ebay/templates/');
        //----------------------------

        //----------------------------
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $this->reviseAllInProcessingState = !is_null(
            Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
                '/ebay/templates/revise/total/', 'last_listing_product_id'
            )
        );

        $this->reviseAllStartDate = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/ebay/templates/revise/total/', 'start_date'
        );
        $this->reviseAllStartDate && $this->reviseAllStartDate = Mage::app()->getLocale()
            ->date(strtotime($this->reviseAllStartDate))
            ->toString($format);

        $this->reviseAllEndDate = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/ebay/templates/revise/total/', 'end_date'
        );
        $this->reviseAllEndDate && $this->reviseAllEndDate = Mage::app()->getLocale()
            ->date(strtotime($this->reviseAllEndDate))
            ->toString($format);
        //----------------------------

        //----------------------------
        $component = Ess_M2ePro_Helper_Component_Ebay::NICK;
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

    // ########################################

    public function isShowReviseAll()
    {
        $showSetting = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/synchronization/revise_total/','show'
        );

        return $showSetting && Mage::helper('M2ePro/View_Ebay')->isAdvancedMode();
    }

    // ########################################
}