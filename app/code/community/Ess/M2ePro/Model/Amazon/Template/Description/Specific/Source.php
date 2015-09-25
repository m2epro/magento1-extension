<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Template_Description_Specific_Source
{
    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var $descriptionSpecificTemplateModel Ess_M2ePro_Model_Amazon_Template_Description_Specific
     */
    private $descriptionSpecificTemplateModel = null;

    // ########################################

    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    // ----------------------------------------

    public function setDescriptionSpecificTemplate(Ess_M2ePro_Model_Amazon_Template_Description_Specific $instance)
    {
        $this->descriptionSpecificTemplateModel = $instance;
        return $this;
    }

    public function getDescriptionSpecificTemplate()
    {
        return $this->descriptionSpecificTemplateModel;
    }

    // ########################################

    public function getPath()
    {
        $xpath = $this->getDescriptionSpecificTemplate()->getXpath();
        $xpathParts = explode('/',$xpath);

        $path = '';
        $isFirst = true;

        foreach ($xpathParts as $part) {
            list($tag,$index) = explode('-',$part);

            if (!$tag) {
                continue;
            }

            $isFirst || $path .= '{"childNodes": ';
            $path .= "{\"$tag\": {\"$index\": ";
            $isFirst = false;
        }

        if ($this->getDescriptionSpecificTemplate()->getMode() == 'none') {

            $path .= '[]';
            $path .= str_repeat('}',substr_count($path,'{'));

            return $path;
        }

        $value = $this->getDescriptionSpecificTemplate()->getData(
            $this->getDescriptionSpecificTemplate()->getMode()
        );

        if ($this->getDescriptionSpecificTemplate()->getMode() == 'custom_attribute') {
            $value = $this->getMagentoProduct()->getAttributeValue(
                $this->getDescriptionSpecificTemplate()->getCustomAttribute()
            );
        }

        $type = $this->getDescriptionSpecificTemplate()->getType();
        $type == 'int' && $value = (int)$value;
        $type == 'float' && $value = (float)str_replace(',','.',$value);
        $type == 'date_time' && $value = str_replace(' ','T',$value);

        $attributes = array();
        foreach ($this->getDescriptionSpecificTemplate()->getAttributes() as $index => $attribute) {

            list($attributeName) = array_keys($attribute);

            $attributeData = $attribute[$attributeName];

            $attributeValue = $attributeData['mode'] == 'custom_value'
                ? $attributeData['custom_value']
                : $this->getMagentoProduct()->getAttributeValue($attributeData['custom_attribute']);

            $attributes[$index] = array(
                'name'  => str_replace(' ','',$attributeName),
                'value' => $attributeValue,
            );
        }

        $attributes = json_encode($attributes);

        $path .= '%data%';
        $path .= str_repeat('}',substr_count($path,'{'));

        $path = str_replace(
            '%data%',
            "{\"value\": ".json_encode($value).",\"attributes\": $attributes}",
            $path
        );

        return $path;
    }

    // ########################################
}