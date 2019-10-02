<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Attribute_Builder
{
    const TYPE_TEXT            = 'text';
    const TYPE_TEXTAREA        = 'textarea';
    const TYPE_SELECT          = 'select';
    const TYPE_MULTIPLE_SELECT = 'multiselect';
    const TYPE_BOOLEAN         = 'boolean';
    const TYPE_PRICE           = 'price';
    const TYPE_DATE            = 'date';

    const SCOPE_STORE   = 0;
    const SCOPE_GLOBAL  = 1;
    const SCOPE_WEBSITE = 2;

    const CODE_MAX_LENGTH = 30;

    /** @var Mage_Eav_Model_Entity_Attribute */
    protected $_attributeObj = null;

    protected $_code;
    protected $_primaryLabel;
    protected $_inputType;

    protected $_entityTypeId;

    protected $_options = array();
    protected $_params  = array();

    //########################################

    public function save()
    {
        $this->init();
        return $this->saveAttribute();
    }

    // ---------------------------------------

    protected function init()
    {
        if ($this->_entityTypeId === null) {
            $this->_entityTypeId = Mage::getModel('catalog/product')->getResource()->getTypeId();
        }

        if ($this->_inputType === null) {
            $this->_inputType = self::TYPE_TEXT;
        }

        $this->_attributeObj = Mage::getModel('eav/entity_attribute')
                                   ->loadByCode($this->_entityTypeId, $this->_code);

        return $this;
    }

    protected function saveAttribute()
    {
        if ($this->_attributeObj->getId()) {
            return array('result' => true, 'obj' => $this->_attributeObj);
        }

        if (!$this->validate()) {
            return array('result' => false, 'error' => 'Attribute builder. Validation failed.');
        }

        $this->_attributeObj = Mage::getModel('catalog/resource_eav_attribute');

        $data = $this->_params;
        $data['attribute_code'] = $this->_code;
        $data['frontend_label'] = array(Mage_Core_Model_App::ADMIN_STORE_ID => $this->_primaryLabel);
        $data['frontend_input'] = $this->_inputType;
        $data['entity_type_id'] = $this->_entityTypeId;
        $data['is_user_defined']   = 1;

        $data['source_model'] = Mage::helper('catalog/product')->getAttributeSourceModelByInputType($this->_inputType);
        $data['backend_model'] = Mage::helper('catalog/product')->getAttributeBackendModelByInputType(
            $this->_inputType
        );
        $data['backend_type'] = $this->_attributeObj->getBackendTypeByInput($this->_inputType);

        !isset($data['is_global'])               && $data['is_global'] = self::SCOPE_STORE;
        !isset($data['is_configurable'])         && $data['is_configurable'] = 0;
        !isset($data['is_filterable'])           && $data['is_filterable'] = 0;
        !isset($data['is_filterable_in_search']) && $data['is_filterable_in_search'] = 0;
        !isset($data['apply_to'])                && $data['apply_to'] = array();

        $this->prepareOptions($data);
        $this->prepareDefault($data);

        $this->_attributeObj->addData($data);

        try {
            $this->_attributeObj->save();
        } catch (Exception $e) {
            return array('result' => false, 'error' => $e->getMessage());
        }

        return array('result' => true, 'obj' => $this->_attributeObj);
    }

    protected function validate()
    {
        $validatorAttrCode = new Zend_Validate_Regex(array('pattern' => '/^[a-z][a-z_0-9]{1,254}$/'));
        if (!$validatorAttrCode->isValid($this->_code)) {
            return false;
        }

        if (strlen($this->_code) > self::CODE_MAX_LENGTH) {
            return false;
        }

        if (empty($this->_primaryLabel)) {
            return false;
        }

        /** @var $validatorInputType Mage_Eav_Model_Adminhtml_System_Config_Source_Inputtype_Validator */
        $validatorInputType = Mage::getModel('eav/adminhtml_system_config_source_inputtype_validator');
        if (!$validatorInputType->isValid($this->_inputType)) {
            return false;
        }

        return true;
    }

    //########################################

    public static function generateCodeByLabel($primaryLabel)
    {
        $attributeCode = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $primaryLabel);
        $attributeCode = preg_replace('/[^0-9a-z]/i', '_', $attributeCode);
        $attributeCode = preg_replace('/_+/', '_', $attributeCode);

        $abc = 'abcdefghijklmnopqrstuvwxyz';
        if (preg_match('/^\d{1}/', $attributeCode, $matches)) {
            $index = $matches[0];
            $attributeCode = $abc[$index].'_'.$attributeCode;
        }

        return strtolower($attributeCode);
    }

    //########################################

    protected function prepareOptions(&$data)
    {
        $options = $this->_options;

        if (!empty($this->_params['default_value'])) {
            if ($this->isSelectType()) {
                $options[] = (string)$this->_params['default_value'];
            }

            if ($this->isMultipleSelectType()) {
                is_array($this->_params['default_value'])
                    ? $options = array_merge($options, $this->_params['default_value'])
                    : $options[] = (string)$this->_params['default_value'];
            }
        }

        foreach (array_unique($options) as $optionValue) {
            $code = $this->getOptionCode($optionValue);
            $data['option']['value'][$code] = array(Mage_Core_Model_App::ADMIN_STORE_ID => $optionValue);
        }
    }

    protected function getOptionCode($optionValue)
    {
        return 'option_'.substr(sha1($optionValue), 0, 6);
    }

    //----------------------------------------

    protected function prepareDefault(&$data)
    {
        if (!isset($this->_params['default_value'])) {
            $this->_params['default_value'] = null;
        }

        if ($this->isDateType() || $this->isTextAreaType() || $this->isTextType()) {
            $data['default_value'] = (string)$this->_params['default_value'];
            return;
        }

        if ($this->isBooleanType()) {
            $data['default_value'] = (int)(strtolower($this->_params['default_value']) == 'yes');
            return;
        }

        if ($this->isSelectType() || $this->isMultipleSelectType()) {
            $defaultValues = is_array($this->_params['default_value']) ? $this->_params['default_value']
                                                                      : array($this->_params['default_value']);

            $data['default_value'] = null;
            foreach ($defaultValues as $value) {
                $data['default'][] = $this->getOptionCode($value);
            }

            return;
        }
    }

    //########################################

    public function setCode($value)
    {
        $this->_code = $value;
        return $this;
    }

    public function setLabel($value)
    {
        $this->_primaryLabel = $value;
        return $this;
    }

    public function setInputType($value)
    {
        $this->_inputType = $value;
        return $this;
    }

    public function setOptions($options)
    {
        $this->_options = $options;
        return $this;
    }

    public function setParams(array $value = array())
    {
        $this->_params = $value;
        return $this;
    }

    /**
     * Can be string|int or array for multi select attribute
     * @param $value
     * @return $this
     */
    public function setDefaultValue($value)
    {
        $this->_params['default_value'] = $value;
        return $this;
    }

    public function setScope($value)
    {
        $this->_params['is_global'] = $value;
        return $this;
    }

    public function setEntityTypeId($value)
    {
        $this->_entityTypeId = $value;
        return $this;
    }

    //########################################

    public function isSelectType()
    {
        return $this->_inputType == self::TYPE_SELECT;
    }

    public function isMultipleSelectType()
    {
        return $this->_inputType == self::TYPE_MULTIPLE_SELECT;
    }

    public function isBooleanType()
    {
        return $this->_inputType == self::TYPE_BOOLEAN;
    }

    public function isTextType()
    {
        return $this->_inputType == self::TYPE_TEXT;
    }

    public function isTextAreaType()
    {
        return $this->_inputType == self::TYPE_TEXTAREA;
    }

    public function isDateType()
    {
        return $this->_inputType == self::TYPE_DATE;
    }

    //########################################
}
