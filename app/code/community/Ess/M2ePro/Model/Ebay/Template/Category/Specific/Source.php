<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Category_Specific_Source
{
    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var $categorySpecificTemplateModel Ess_M2ePro_Model_Ebay_Template_Category_Specific
     */
    private $categorySpecificTemplateModel = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return $this
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category_Specific $instance
     * @return $this
     */
    public function setCategorySpecificTemplate(Ess_M2ePro_Model_Ebay_Template_Category_Specific $instance)
    {
        $this->categorySpecificTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category_Specific
     */
    public function getCategorySpecificTemplate()
    {
        return $this->categorySpecificTemplateModel;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getCategoryTemplate()
    {
        return $this->getCategorySpecificTemplate()->getCategoryTemplate();
    }

    //########################################

    public function getLabel()
    {
        if ($this->getCategorySpecificTemplate()->isCustomItemSpecificsMode() &&
            $this->getCategorySpecificTemplate()->isCustomAttributeValueMode()) {
            return $this->getAttributeLabel();
        }

        return $this->getCategorySpecificTemplate()->getData('attribute_title');
    }

    public function getValues()
    {
        $valueData = array();

        if ($this->getCategorySpecificTemplate()->isNoneValueMode()) {
            $valueData[] = '--';
        }

        if ($this->getCategorySpecificTemplate()->isEbayRecommendedValueMode()) {
            $valueData = json_decode($this->getCategorySpecificTemplate()->getData('value_ebay_recommended'),true);
        }

        if ($this->getCategorySpecificTemplate()->isCustomValueValueMode()) {
            $valueData = json_decode($this->getCategorySpecificTemplate()->getData('value_custom_value'),true);
        }

        if (!$this->getCategorySpecificTemplate()->isCustomAttributeValueMode() &&
            !$this->getCategorySpecificTemplate()->isCustomLabelAttributeValueMode()) {
            return $valueData;
        }

        $attributeCode = $this->getCategorySpecificTemplate()->getData('value_custom_attribute');
        $valueTemp = $this->getAttributeValue($attributeCode);

        $categoryId = $this->getCategoryTemplate()->getCategoryMainId();
        $marketplaceId = $this->getCategoryTemplate()->getMarketplaceId();

        if (empty($categoryId) || empty($marketplaceId) || strpos($valueTemp, ',') === false ||
            $this->getMagentoProduct()->getAttributeFrontendInput($attributeCode) !== 'multiselect') {

            $valueData[] = $valueTemp;
            return $valueData;
        }

        $specifics = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
            ->getSpecifics($categoryId, $marketplaceId);

        if (empty($specifics)) {
            $valueData[] = $valueTemp;
            return $valueData;
        }

        foreach ($specifics as $specific) {

            if ($specific['title'] === $this->getCategorySpecificTemplate()->getData('attribute_title') &&
                in_array($specific['type'],array('select_multiple_or_text','select_multiple'))) {

                foreach (explode(',', $valueTemp) as $val) {
                    $valueData[] =  trim($val);
                }

                return $valueData;
            }
        }

        $valueData[] = $valueTemp;
        return $valueData;
    }

    //########################################

    private function getAttributeLabel()
    {
        return Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel(
                    $this->getCategorySpecificTemplate()->getData('value_custom_attribute'),
                    $this->getMagentoProduct()->getStoreId()
                );
    }

    private function getAttributeValue($attributeCode)
    {
        $attributeValue = $this->getMagentoProduct()->getAttributeValue($attributeCode);

        if ($attributeCode == 'country_of_manufacture') {
            $locale = Mage::getStoreConfig(
                Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE, $this->getMagentoProduct()->getStoreId()
            );

            if ($countryName = Mage::helper('M2ePro/Magento')->getTranslatedCountryName($attributeValue, $locale)) {
                $attributeValue = $countryName;
            }
        }

        return $attributeValue;
    }

    //########################################
}