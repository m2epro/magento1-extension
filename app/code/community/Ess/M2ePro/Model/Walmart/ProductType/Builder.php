<?php

use Ess_M2ePro_Model_Resource_Walmart_ProductType as ProductTypeResource;

class Ess_M2ePro_Model_Walmart_ProductType_Builder
    extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    /** @var Ess_M2ePro_Model_Walmart_Dictionary_ProductType_Repository  */
    private $productTypeDictionaryRepository;
    /** @var Ess_M2ePro_Model_Walmart_Marketplace_Repository */
    private $marketplaceRepository;
    private $attributesSettings = array();

    public function __construct()
    {
        $this->productTypeDictionaryRepository = Mage::getModel('M2ePro/Walmart_Dictionary_ProductType_Repository');
        $this->marketplaceRepository = Mage::getModel('M2ePro/Walmart_Marketplace_Repository');
    }

    protected function prepareData()
    {
        if ($this->_model->getId()) {
            $data = $this->_model->getData();
        } else {
            $data = $this->getDefaultData();

            $temp = array();
            $keys = array('marketplace_id', 'nick');
            foreach ($keys as $key) {
                if (empty($this->_rawData['general'][$key])) {
                    throw new Ess_M2ePro_Model_Exception(
                        "Missing required field for Product Type: $key"
                    );
                }

                $temp[$key] = $this->_rawData['general'][$key];
            }

            $marketplace = $this->marketplaceRepository->get((int)$temp['marketplace_id']);
            if (
                !$marketplace->getChildObject()
                             ->isSupportedProductType()
            ) {
                throw new LogicException('Marketplace not supported Product Types');
            }

            $dictionary = $this->productTypeDictionaryRepository->findByNick(
                $temp['nick'],
                (int)$temp['marketplace_id']
            );
            if ($dictionary === null) {
                throw new Ess_M2ePro_Model_Exception(
                    "Product Type data not found for provided marketplace_id and product type nick"
                );
            }

            $data[ProductTypeResource::COLUMN_DICTIONARY_PRODUCT_TYPE_ID] = $dictionary->getId();
        }

        if (isset($this->_rawData['general']['product_type_title'])) {
            $data[ProductTypeResource::COLUMN_TITLE] = $this->_rawData['general']['product_type_title'];
        }

        if (!empty($this->_rawData['field_data']) && is_array($this->_rawData['field_data'])) {
            $this->attributesSettings = array();
            $this->collectAttributesSettings($this->_rawData['field_data']);

            $data[ProductTypeResource::COLUMN_ATTRIBUTES_SETTINGS] = Mage::helper('M2ePro')->jsonEncode(
                $this->attributesSettings
            );
        }

        return $data;
    }

    /**
     * @param array $data
     * @param array $path
     * @return void
     */
    private function collectAttributesSettings(array $data, array $path = array())
    {
        $pathString = implode("/", $path);
        foreach ($data as $key => $value) {
            if (isset($value['mode']) && is_string($value['mode'])) {
                if (!isset($this->attributesSettings[$pathString])) {
                    $this->attributesSettings[$pathString] = array();
                }

                if ($fieldData = $this->collectFieldData($value, $path)) {
                    $this->attributesSettings[$pathString][] = $fieldData;
                }
            } else {
                $currentPath = $path;
                $currentPath[] = $key;
                $this->collectAttributesSettings($value, $currentPath);
            }
        }

        if (empty($this->attributesSettings[$pathString])) {
            unset($this->attributesSettings[$pathString]);
        }
    }

    /**
     * @return array
     */
    private function collectFieldData(array $field)
    {
        if (empty($field['mode'])) {
            return array();
        }

        switch ((int)$field['mode']) {
            case Ess_M2ePro_Model_Walmart_ProductType::FIELD_CUSTOM_VALUE:
                if (!empty($field['format']) && $field['format'] === 'date-time') {
                    $timestamp = Mage::helper('M2ePro')->getCurrentTimezoneDate($field['value'])->getTimestamp();
                    $datetime = Mage::helper('M2ePro')->createCurrentGmtDateTime();
                    $datetime->setTimestamp($timestamp);

                    $field['value'] = $datetime->format('Y-m-d H:i:s');
                }

                return array(
                    'mode' => (int)$field['mode'],
                    'value' => $field['value'],
                );
            case Ess_M2ePro_Model_Walmart_ProductType::FIELD_CUSTOM_ATTRIBUTE:
                if (empty($field['attribute_code'])) {
                    return array();
                }

                return array(
                    'mode' => (int)$field['mode'],
                    'attribute_code' => $field['attribute_code'],
                );
            default:
                throw new Ess_M2ePro_Model_Exception('Incorrect mode for Product Type attributes settings.');
        }
    }

    /**
     * @return array
     */
    public function getDefaultData()
    {
        return array(
            ProductTypeResource::COLUMN_ID => '',
            ProductTypeResource::COLUMN_TITLE => '',
            ProductTypeResource::COLUMN_DICTIONARY_PRODUCT_TYPE_ID => '',
            ProductTypeResource::COLUMN_ATTRIBUTES_SETTINGS => '[]',
        );
    }
}
