<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute extends Ess_M2ePro_Model_Component_Abstract
{
    const ATTRIBUTE_MODE_NONE              = 0;
    const ATTRIBUTE_MODE_CUSTOM_VALUE      = 1;
    const ATTRIBUTE_MODE_CUSTOM_ATTRIBUTE  = 2;
    const ATTRIBUTE_MODE_RECOMMENDED_VALUE = 3;

    const TYPE_SELECT      = 1;
    const TYPE_MULTISELECT = 2;
    const TYPE_INT         = 3;
    const TYPE_STRING      = 4;
    const TYPE_DECIMAL     = 5;

    const TYPE_IS_REQUIRED = 1;

    /**
     * @var Ess_M2ePro_Model_Buy_Template_NewProduct
     */
    private $newProductTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute_Source[]
     */
    private $newProductAttributeSourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Template_NewProduct_Attribute');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->newProductTemplateModel = NULL;
        $temp && $this->newProductAttributeSourceModels = array();
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Buy_Template_NewProduct
     */
    public function getNewProductTemplate()
    {
        if (is_null($this->newProductTemplateModel)) {

            $this->newProductTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                'Buy_Template_NewProduct', $this->getTemplateNewProductId(), NULL, array('template')
            );
        }

        return $this->newProductTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Buy_Template_NewProduct $instance
     */
    public function setNewProductTemplate(Ess_M2ePro_Model_Buy_Template_NewProduct $instance)
    {
        $this->newProductTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Buy_Template_NewProduct_Attribute_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->newProductAttributeSourceModels[$productId])) {
            return $this->newProductAttributeSourceModels[$productId];
        }

        $this->newProductAttributeSourceModels[$productId] = Mage::getModel(
            'M2ePro/Buy_Template_NewProduct_Attribute_Source'
        );
        $this->newProductAttributeSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->newProductAttributeSourceModels[$productId]->setNewProductAttributeTemplate($this);

        return $this->newProductAttributeSourceModels[$productId];
    }

    //########################################

    /**
     * @return int
     */
    public function getTemplateNewProductId()
    {
        return (int)$this->getData('template_new_product_id');
    }

    public function getName()
    {
        return $this->getData('attribute_name');
    }

    /**
     * @return int
     */
    public function getMode()
    {
        return (int)$this->getData('mode');
    }

    public function getRecommendedValue()
    {
        $value = $this->getData('recommended_value');
        return is_null($value) ? array() : json_decode($value, true);
    }

    public function getCustomValue()
    {
        return $this->getData('custom_value');
    }

    public function getCustomAttribute()
    {
        return $this->getData('custom_attribute');
    }

    /**
     * @return array
     */
    public function getAttributeSource()
    {
        return array(
            'mode' => $this->getMode(),
            'name' => $this->getName(),
            'recommended_value' => $this->getRecommendedValue(),
            'custom_value'      => $this->getCustomValue(),
            'custom_attribute'  => $this->getCustomAttribute(),
        );
    }

    //########################################
}