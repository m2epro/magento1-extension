<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * Class Ess_M2ePro_Block_Adminhtml_Walmart_Configuration_General_Form
 *
 * @method Ess_M2ePro_Helper_Magento_Attribute getMagentoAttributeHelper()
 * @method Ess_M2ePro_Helper_Component_Walmart_Configuration getConfigurationHelper()
 * @method bool getShowWarning()
 * @method $this setShowWarning(bool $showWarning)
 * @method array getAllAttributes()
 * @method array getTextAttributes()
 */
class Ess_M2ePro_Block_Adminhtml_Walmart_Configuration_General_Form extends Mage_Adminhtml_Block_Widget_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartConfigurationGeneralForm');
        $this->setContainerId('magento_block_walmart_configuration_general');
        $this->setTemplate('M2ePro/walmart/configuration/general/form.phtml');
        // ---------------------------------------
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/adminhtml_walmart_configuration/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $this->setData('configuration_helper', Mage::helper('M2ePro/Component_Walmart_Configuration'));
        $this->setData('magento_attribute_helper', Mage::helper('M2ePro/Magento_Attribute'));
        $this->setData('show_warning', false);
        $this->setData('all_attributes', $this->getMagentoAttributeHelper()->getAll());
        $this->setData(
            'text_attributes',
            $this->getMagentoAttributeHelper()->filterByInputTypes($this->getAllAttributes(), array('text'))
        );

        return parent::_beforeToHtml();
    }

    //########################################
}
