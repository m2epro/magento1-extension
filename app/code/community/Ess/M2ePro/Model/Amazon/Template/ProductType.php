<?php


class Ess_M2ePro_Model_Amazon_Template_ProductType extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    const FIELD_NOT_CONFIGURED = 0;
    const FIELD_CUSTOM_VALUE = 1;
    const FIELD_CUSTOM_ATTRIBUTE = 2;

    const VIEW_MODE_ALL_ATTRIBUTES = 0;
    const VIEW_MODE_REQUIRED_ATTRIBUTES = 1;

    const GENERAL_PRODUCT_TYPE_NICK = 'PRODUCT';

    /**
     * @var Ess_M2ePro_Model_Amazon_Dictionary_ProductType
     */
    private $dictionary;

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Template_ProductType');
    }

    public function getDictionaryProductTypeId()
    {
        return (int)$this->getData(
            Ess_M2ePro_Model_Resource_Amazon_Template_ProductType::COLUMN_DICTIONARY_PRODUCT_TYPE_ID
        );
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Dictionary_ProductType
     */
    public function getDictionary()
    {
        if (!isset($this->dictionary)) {
            $dictionaryRepository = Mage::getModel('M2ePro/Amazon_Dictionary_ProductType_Repository');
            $this->dictionary = $dictionaryRepository->get(
                $this->getDictionaryProductTypeId()
            );
        }

        return $this->dictionary;
    }

    public function getMarketplaceId()
    {
        return $this->getDictionary()->getMarketplaceId();
    }

    public function getNick()
    {
        return $this->getDictionary()->getNick();
    }

    public function getTitle()
    {
        return $this->getData(Ess_M2ePro_Model_Resource_Amazon_Template_ProductType::COLUMN_TITLE);
    }

    public function getCustomAttributesName()
    {
        $specifics = $this->getSelfSetting();
        $customAttributes = array();
        foreach ($specifics as $values) {
            foreach ($values as $value) {
                if (!isset($value['mode'])) {
                    continue;
                }

                if ((int)$value['mode'] === self::FIELD_CUSTOM_ATTRIBUTE) {
                    $customAttributes[] = $value['attribute_code'];
                }
            }
        }

        return array_unique($customAttributes);
    }

    public function getCustomAttributesList()
    {
        $result = array();
        foreach ($this->getCustomAttributes() as $attributeName => $values) {
            foreach ($values as $value) {
                if ((int)$value['mode'] !== self::FIELD_CUSTOM_ATTRIBUTE) {
                    continue;
                }

                $result[] = array(
                    'name' => $attributeName,
                    'attribute_code' => $value['attribute_code']
                );
            }
        }

        return $result;
    }

    private function getCustomAttributes()
    {
        $specifics = $this->getSelfSetting();
        $fieldCustomAttribute = self::FIELD_CUSTOM_ATTRIBUTE;

        $filterCallback = function (array $values) use ($fieldCustomAttribute) {
            foreach ($values as $value) {
                if (!isset($value['mode'])) {
                    continue;
                }

                return (int)$value['mode'] === $fieldCustomAttribute;
            }

            return false;
        };

        return array_filter($specifics, $filterCallback);
    }

    public function getViewMode()
    {
        $viewMode = $this->getData(Ess_M2ePro_Model_Resource_Amazon_Template_ProductType::COLUMN_VIEW_MODE);
        if ($viewMode === null) {
            return self::VIEW_MODE_REQUIRED_ATTRIBUTES;
        }

        return (int)$viewMode;
    }

    public function getSelfSetting()
    {
        $value = $this->getData(Ess_M2ePro_Model_Resource_Amazon_Template_ProductType::COLUMN_SETTINGS);
        if (empty($value)) {
            return array();
        }

        return (array)json_decode($value, true);
    }
}