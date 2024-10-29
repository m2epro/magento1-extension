<?php

class Ess_M2ePro_Block_Adminhtml_Walmart_ProductType_Edit_FieldTemplates
    extends Mage_Adminhtml_Block_Widget
{
    protected $_template = 'M2ePro/walmart/productType/edit/field_templates.phtml';

    /** @var array */
    private $attributes;

   public function __construct(array $args = array())
   {
       /** @var Ess_M2ePro_Helper_Magento_Attribute $magentoAttributeHelper */
       $magentoAttributeHelper = Mage::helper('M2ePro/Magento_Attribute');
       $this->attributes = $magentoAttributeHelper->filterAllAttrByInputTypes(
           array(
               'boolean',
               'date',
               'gallery',
               'hidden',
               'image',
               'media_image',
               'multiline',
               'price',
               'select',
               'text',
               'textarea',
               'weight',
               'multiselect',
           )
       );

       parent::__construct($args);
   }

    /**
     * @return array
     */
    public function getAvailableAttributes()
    {
        return $this->attributes;
    }
}
