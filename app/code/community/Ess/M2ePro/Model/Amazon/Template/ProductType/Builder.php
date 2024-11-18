<?php

class Ess_M2ePro_Model_Amazon_Template_ProductType_Builder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    /** @var array */
    private $otherImagesSpecifics;
    /** @var Ess_M2ePro_Model_Amazon_Dictionary_ProductType_Repository */
    private $dictionaryProductTypeRepository;
    private $specifics = array();

    public function __construct(

    ) {
        $this->otherImagesSpecifics = Ess_M2ePro_Helper_Component_Amazon_ProductType::getOtherImagesSpecifics();
        $this->dictionaryProductTypeRepository = Mage::getModel('M2ePro/Amazon_Dictionary_ProductType_Repository');
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

            $dictionary = $this->dictionaryProductTypeRepository->findByMarketplaceAndNick(
                (int)$temp['marketplace_id'],
                (string)$temp['nick']
            );
            if ($dictionary === null) {
                throw new Ess_M2ePro_Model_Exception(
                    "Product Type data not found for provided marketplace_id and product type nick"
                );
            }

            $data['dictionary_product_type_id'] = $dictionary->getId();
        }

        if (isset($this->_rawData['general']['product_type_title'])) {
            $data['title'] = $this->_rawData['general']['product_type_title'];
        }

        if (!empty($this->_rawData['field_data']) && is_array($this->_rawData['field_data'])) {
            $this->specifics = array();
            $this->collectSpecifics($this->_rawData['field_data']);

            $data['settings'] = Zend_Json::encode($this->specifics);
        }

        $data['view_mode'] = 0;

        return $data;
    }

    /**
     * @param array $data
     * @param array $path
     *
     * @return void
     * @throws Ess_M2ePro_Model_Exception
     */
    private function collectSpecifics(array $data, array $path = array())
    {
        $pathString = implode("/", $path);
        foreach ($data as $key => $value) {
            if (isset($value['mode']) && is_string($value['mode'])) {
                if (!isset($this->specifics[$pathString])) {
                    $this->specifics[$pathString] = array();
                }

                if ($fieldData = $this->collectFieldData($value, $path)) {
                    $this->specifics[$pathString][] = $fieldData;
                }
            } else {
                $currentPath = $path;
                $currentPath[] = $key;
                $this->collectSpecifics($value, $currentPath);
            }
        }

        if (empty($this->specifics[$pathString])) {
            unset($this->specifics[$pathString]);
        }
    }

    /**
     * @throws Ess_M2ePro_Model_Exception
     */
    private function collectFieldData(array $field, array $path)
    {
        if (empty($field['mode'])) {
            return array();
        }

        switch ((int)$field['mode']) {
            case Ess_M2ePro_Model_Amazon_Template_ProductType::FIELD_CUSTOM_VALUE:
                if (!empty($field['format']) && $field['format'] === 'date-time') {
                    $datetime = Mage::helper('M2ePro')->timezoneDateToGmt($field['value']);

                    $field['value'] = $datetime;
                }

                return array(
                    'mode' => (int)$field['mode'],
                    'value' => $field['value'],
                );
            case Ess_M2ePro_Model_Amazon_Template_ProductType::FIELD_CUSTOM_ATTRIBUTE:
                if (empty($field['attribute_code'])) {
                    return array();
                }

                $key = implode('/', $path);
                if (in_array($key, $this->otherImagesSpecifics)) {
                    if (is_numeric($field['attribute_code'])) {
                        return array(
                            'mode' => (int)$field['mode'],
                            'attribute_code' => 'media_gallery',
                            'images_limit' => (int)$field['attribute_code'],
                        );
                    }
                }

                return array(
                    'mode' => (int)$field['mode'],
                    'attribute_code' => $field['attribute_code'],
                );
            default:
                throw new Ess_M2ePro_Model_Exception('Incorrect mode for Product Type specifics.');
        }
    }

    /**
     * @return array
     */
    public function getDefaultData()
    {
        return array(
            'id' => '',
            'title' => '',
            'view_mode' => Ess_M2ePro_Model_Amazon_Template_ProductType::VIEW_MODE_ALL_ATTRIBUTES,
            'dictionary_product_type_id' => '',
            'settings' => '',
        );
    }
}