<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_Configuration_General_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /** @var Ess_M2ePro_Helper_Component_Amazon_Configuration */
    private $configurationHelper;

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonConfigurationGeneralForm');
        $this->setContainerId('magento_block_amazon_configuration_general');
        $this->setTemplate('M2ePro/amazon/configuration/general/form.phtml');
        // ---------------------------------------

        $this->configurationHelper = Mage::helper('M2ePro/Component_Amazon_Configuration');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/adminhtml_amazon_configuration/save'),
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getConfigurationHelper()
    {
        return $this->configurationHelper;
    }

    /**
     * @return array
     */
    public function getMagentoAttributes()
    {
        /** @var Ess_M2ePro_Helper_Magento_Attribute $magentoAttributeHelper */
        $magentoAttributeHelper = Mage::helper('M2ePro/Magento_Attribute');
        $attributes = $magentoAttributeHelper->getAll();

       return $magentoAttributeHelper->filterByInputTypes(
           $attributes,
           array('text', 'select')
       );
    }
}
