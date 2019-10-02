<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_Settings_Form extends Ess_M2ePro_Block_Adminhtml_Configuration_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('configurationSettingsForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/configuration/settings.phtml');

        // ---------------------------------------

        $this->setPageHelpLink('x/CwAJAQ');
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
            'id'      => 'config_edit_form',
            'action'  => $this->getUrl('M2ePro/adminhtml_configuration_settings/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Configuration/SettingsHandler.js')
            ->addJs('M2ePro/Template/EditHandler.js');
    }

    protected function _beforeToHtml()
    {
        // Set data for form
        // ---------------------------------------
        $this->products_show_thumbnails = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/', 'show_products_thumbnails'
        );
        $this->block_notices_show = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/', 'show_block_notices'
        );
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Restore All Helps & Remembered Choices'),
            'onclick' => 'SettingsHandlerObj.restoreAllHelpsAndRememberedChoices()',
            'class'   => 'restore_block_notices'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('restore_block_notices', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $this->forceQtyMode = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/product/force_qty/', 'mode'
        );
        $this->forceQtyValue = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/product/force_qty/', 'value'
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->priceConvertMode = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/magento/attribute/', 'price_type_converting'
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->inspectorMode = (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/listing/product/inspector/', 'mode'
        );
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}
