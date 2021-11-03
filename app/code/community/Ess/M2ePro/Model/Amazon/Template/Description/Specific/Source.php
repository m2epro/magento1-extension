<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Template_Description_Specific as DescriptionSpecific;

class Ess_M2ePro_Model_Amazon_Template_Description_Specific_Source
{
    /**
     * @var $_magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    protected $_magentoProduct = null;

    /**
     * @var $_descriptionSpecificTemplateModel Ess_M2ePro_Model_Amazon_Template_Description_Specific
     */
    protected $_descriptionSpecificTemplateModel = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return $this
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->_magentoProduct = $magentoProduct;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->_magentoProduct;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Amazon_Template_Description_Specific $instance
     * @return $this
     */
    public function setDescriptionSpecificTemplate(Ess_M2ePro_Model_Amazon_Template_Description_Specific $instance)
    {
        $this->_descriptionSpecificTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description_Specific
     */
    public function getDescriptionSpecificTemplate()
    {
        return $this->_descriptionSpecificTemplateModel;
    }

    //########################################

    /**
     * @return string
     */
    public function getPath()
    {
        $xpath = $this->getDescriptionSpecificTemplate()->getXpath();
        $xpathParts = explode('/', $xpath);

        $path = '';
        $isFirst = true;

        foreach ($xpathParts as $part) {
            list($tag,$index) = explode('-', $part);

            if (!$tag) {
                continue;
            }

            $isFirst || $path .= '{"childNodes": ';
            $path .= "{\"$tag\": {\"$index\": ";
            $isFirst = false;
        }

        $templateObj = $this->getDescriptionSpecificTemplate();

        if ($templateObj->isModeNone()) {
            $path .= '[]';
            $path .= str_repeat('}', substr_count($path, '{'));

            return $path;
        }

        $path .= '%data%';
        $path .= str_repeat('}', substr_count($path, '{'));

        $encodedValue = Mage::helper('M2ePro')->jsonEncode($this->getValue());
        $path = str_replace(
            '%data%',
            '{"value": ' .$encodedValue. ',"attributes": ' .$this->getValueAttributes(). '}',
            $path
        );

        return $path;
    }

    public function getValue()
    {
        $templateObj = $this->getDescriptionSpecificTemplate();

        if ($templateObj->isModeNone()) {
            return false;
        }

        $value = $templateObj->getData($templateObj->getMode());

        if ($templateObj->isModeCustomAttribute()) {
            $value = $this->getMagentoProduct()->getAttributeValue($value, false);
        }

        $templateObj->isTypeInt()      && $value = (int)$value;
        $templateObj->isTypeFloat()    && $value = (float)str_replace(',', '.', $value);
        $templateObj->isTypeDateTime() && $value = str_replace(' ', 'T', $value);
        $templateObj->isTypeBoolean()  && $value = $this->convertValueToBoolean($value);

        return $value;
    }

    public function getValueAttributes()
    {
        $templateObj = $this->getDescriptionSpecificTemplate();

        $attributes = array();

        foreach ($templateObj->getAttributes() as $index => $attribute) {
            list($attributeName) = array_keys($attribute);

            $attributeData = $attribute[$attributeName];

            $attributeValue = $attributeData['mode'] == DescriptionSpecific::DICTIONARY_MODE_CUSTOM_VALUE
                ? $attributeData['custom_value']
                : $this->getMagentoProduct()->getAttributeValue($attributeData['custom_attribute'], false);

            $attributes[$index] = array(
                'name'  => str_replace(' ', '', $attributeName),
                'value' => $attributeValue,
            );
        }

        return Mage::helper('M2ePro')->jsonEncode($attributes);
    }

    //########################################

    private function convertValueToBoolean($value)
    {
        if ($value === true) {
            $value = 'true';
        } elseif ($value === false) {
            $value = 'false';
        }

        return $value;
    }

    //########################################
}
