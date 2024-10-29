<?php

use Ess_M2ePro_Model_Resource_Walmart_ProductType as ProductTypeResource;

class Ess_M2ePro_Model_Walmart_ProductType extends Ess_M2ePro_Model_Component_Abstract
{
    const FIELD_NOT_CONFIGURED = 0;
    const FIELD_CUSTOM_VALUE = 1;
    const FIELD_CUSTOM_ATTRIBUTE = 2;

    /** @var Ess_M2ePro_Model_Walmart_Dictionary_ProductType_Repository */
    private $dictionaryProductTypeRepository;

    public function __construct()
    {
        parent::__construct();
        
        $this->dictionaryProductTypeRepository = Mage::getModel(
            'M2ePro/Walmart_Dictionary_ProductType_Repository'
        );
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_ProductType');
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(ProductTypeResource::COLUMN_TITLE);
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return $this->getDictionary()->getNick();
    }

    /**
     * @return int
     */
    public function getDictionaryId()
    {
        return (int)$this->getData(ProductTypeResource::COLUMN_DICTIONARY_PRODUCT_TYPE_ID);
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return $this->getDictionary()->getMarketplaceId();
    }

    /**
     * @return string[]
     */
    public function getVariationAttributes()
    {
        return $this->getDictionary()->getVariationAttributes();
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Dictionary_ProductType
     */
    public function getDictionary()
    {
        return $this->dictionaryProductTypeRepository->get(
            $this->getDictionaryId()
        );
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_ProductType_AttributeSetting[]
     */
    public function getAttributesSettings()
    {
        $settings = array();
        foreach ($this->getRawAttributesSettings() as $attributeName => $values) {
            $attributeSetting = new Ess_M2ePro_Model_Walmart_ProductType_AttributeSetting($attributeName);
            foreach ($values as $value) {
                if ($value['mode'] === self::FIELD_CUSTOM_VALUE) {
                    $attributeSetting->addValue(
                        Ess_M2ePro_Model_Walmart_ProductType_AttributeSetting_Value::createAsCustom(
                            $value['value']
                        )
                    );
                    continue;
                }
                if ($value['mode'] === self::FIELD_CUSTOM_ATTRIBUTE) {
                    $attributeSetting->addValue(
                        Ess_M2ePro_Model_Walmart_ProductType_AttributeSetting_Value::createAsProductAttributeCode(
                            $value['attribute_code']
                        )
                    );
                }
            }
            $settings[] = $attributeSetting;
        }

        return $settings;
    }

    /**
     * @return array
     */
    public function getRawAttributesSettings()
    {
        $settings = $this->getData(ProductTypeResource::COLUMN_ATTRIBUTES_SETTINGS);
        if (empty($settings)) {
            return array();
        }

        return json_decode($settings, true);
    }
}