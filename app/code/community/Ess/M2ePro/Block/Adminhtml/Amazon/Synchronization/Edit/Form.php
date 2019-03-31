<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Synchronization_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonSynchronizationForm');
        $this->setContainerId('magento_block_amazon_synchronization');
        $this->setTemplate('M2ePro/amazon/synchronization/edit/form.phtml');
        // ---------------------------------------
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
        $this->instructionsMode = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/cron/task/amazon/listing/product/process_instructions/', 'mode');
        // ---------------------------------------

        // ---------------------------------------
        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $lastListingProductId = Mage::getModel('M2ePro/Registry')
            ->load('/listing/product/revise/total/amazon/last_listing_product_id/', 'key')
            ->getValue();

        $this->reviseAllInProcessingState = !empty($lastListingProductId);

        $this->reviseAllStartDate = Mage::getModel('M2ePro/Registry')
            ->load('/listing/product/revise/total/amazon/start_date/', 'key')
            ->getValue();
        $this->reviseAllStartDate && $this->reviseAllStartDate = Mage::app()->getLocale()
            ->date(strtotime($this->reviseAllStartDate))
            ->toString($format);

        $this->reviseAllEndDate = Mage::getModel('M2ePro/Registry')
            ->load('/listing/product/revise/total/amazon/end_date/', 'key')
            ->getValue();
        $this->reviseAllEndDate && $this->reviseAllEndDate = Mage::app()->getLocale()
            ->date(strtotime($this->reviseAllEndDate))
            ->toString($format);
        // ---------------------------------------

        // ---------------------------------------
        $component = Ess_M2ePro_Helper_Component_Amazon::NICK;
        $data = array(
            'class'   => 'ok_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => "Windows.getFocusedWindow().close(); SynchronizationHandlerObj.runReviseAll('{$component}');",
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('revise_all_confirm_popup_ok_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $this->inspectorMode = (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/listing/product/inspector/', 'mode'
        );
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    public function isShowReviseAll()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/synchronization/revise_total/','show'
        );
    }

    //########################################
}