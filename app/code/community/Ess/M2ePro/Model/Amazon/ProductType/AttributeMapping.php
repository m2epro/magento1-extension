<?php

use Ess_M2ePro_Model_Resource_Amazon_ProductType_AttributeMapping as Resource;

class Ess_M2ePro_Model_Amazon_ProductType_AttributeMapping
    extends Ess_M2ePro_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();

        $this->_init('M2ePro/Amazon_ProductType_AttributeMapping');
    }

    /**
     * @param string $productTypeAttributeCode
     * @param string $magentoAttributeCode
     * @param string $productTypeAttributeName
     * @return self
     */
    public function create(
        $productTypeAttributeCode,
        $productTypeAttributeName,
        $magentoAttributeCode
    ) {
        $this->setData(Resource::COLUMN_PRODUCT_TYPE_ATTRIBUTE_CODE, $productTypeAttributeCode);
        $this->setData(Resource::COLUMN_PRODUCT_TYPE_ATTRIBUTE_NAME, $productTypeAttributeName);
        $this->setData(Resource::COLUMN_MAGENTO_ATTRIBUTE_CODE, $magentoAttributeCode);

        return $this;
    }

    /**
     * @return string
     */
    public function getProductTypeAttributeCode()
    {
        return (string)$this->getData('product_type_attribute_code');
    }

    /**
     * @return string
     */
    public function getProductTypeAttributeName()
    {
        return (string)$this->getData('product_type_attribute_name');
    }

    /**
     * @return string
     */
    public function getMagentoAttributeCode()
    {
        return (string)$this->getData('magento_attribute_code');
    }

    /**
     * @param string $attributeCode
     * @return self
     */
    public function setMagentoAttributeCode($attributeCode)
    {
        $this->setData('magento_attribute_code', $attributeCode);

        return $this;
    }
}
