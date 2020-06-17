<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ActiveRecord_Serializer
{
    const SETTING_FIELD_TYPE_JSON          = 'json';
    const SETTING_FIELD_TYPE_SERIALIZATION = 'serialization';

    /** @var Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract */
    protected $_model;

    //########################################

    public function setModel(Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract $model)
    {
        $this->_model = $model;
        return $this;
    }

    //########################################

    /**
     * @param string $fieldName
     * @param string $encodeType
     *
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getSettings(
        $fieldName,
        $encodeType = self::SETTING_FIELD_TYPE_JSON
    ) {
        if (null === $this->_model) {
            throw new Ess_M2ePro_Model_Exception_Logic('Model was not set');
        }

        $settings = $this->_model->getData((string)$fieldName);

        if ($settings === null) {
            return array();
        }

        if ($encodeType === self::SETTING_FIELD_TYPE_JSON) {
            $settings = Mage::helper('M2ePro')->jsonDecode($settings);
        } elseif ($encodeType === self::SETTING_FIELD_TYPE_SERIALIZATION) {
            $settings = Mage::helper('M2ePro')->unserialize($settings);
        } else {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'Encoding type "%encode_type%" is not supported.', $encodeType
            );
        }

        return !empty($settings) ? $settings : array();
    }

    /**
     * @param string $fieldName
     * @param string|array $settingNamePath
     * @param mixed $defaultValue
     * @param string $encodeType
     *
     * @return mixed|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getSetting(
        $fieldName,
        $settingNamePath,
        $defaultValue = NULL,
        $encodeType = self::SETTING_FIELD_TYPE_JSON
    ) {
        if (empty($settingNamePath)) {
            return $defaultValue;
        }

        $settings = $this->getSettings($fieldName, $encodeType);

        !is_array($settingNamePath) && $settingNamePath = array($settingNamePath);

        foreach ($settingNamePath as $pathPart) {
            if (!isset($settings[$pathPart])) {
                return $defaultValue;
            }

            $settings = $settings[$pathPart];
        }

        if (is_numeric($settings)) {
            $settings = ctype_digit($settings) ? (int)$settings : $settings;
        }

        return $settings;
    }

    // ---------------------------------------

    /**
     * @param string $fieldName
     * @param array  $settings
     * @param string $encodeType
     *
     * @return Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function setSettings(
        $fieldName,
        array $settings = array(),
        $encodeType = self::SETTING_FIELD_TYPE_JSON
    ) {
        if (null === $this->_model) {
            throw new Ess_M2ePro_Model_Exception_Logic('Model was not set');
        }

        if ($encodeType == self::SETTING_FIELD_TYPE_JSON) {
            $settings = Mage::helper('M2ePro')->jsonEncode($settings);
        } elseif ($encodeType == self::SETTING_FIELD_TYPE_SERIALIZATION) {
            $settings = Mage::helper('M2ePro')->serialize($settings);
        } else {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'Encoding type "%encode_type%" is not supported.', $encodeType
            );
        }

        $this->_model->setData((string)$fieldName, $settings);
        return $this->_model;
    }

    /**
     * @param string $fieldName
     * @param string|array $settingNamePath
     * @param mixed $settingValue
     * @param string $encodeType
     *
     * @return Ess_M2ePro_Model_ActiveRecord_ActiveRecordAbstract
     *
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function setSetting(
        $fieldName,
        $settingNamePath,
        $settingValue,
        $encodeType = self::SETTING_FIELD_TYPE_JSON
    ) {
        if (empty($settingNamePath)) {
            return $this->_model;
        }

        $settings = $this->getSettings($fieldName, $encodeType);
        $target = &$settings;

        !is_array($settingNamePath) && $settingNamePath = array($settingNamePath);

        $currentPathNumber = 0;
        $totalPartsNumber = count($settingNamePath);

        foreach ($settingNamePath as $pathPart) {
            $currentPathNumber++;

            if (!array_key_exists($pathPart, $settings) && $currentPathNumber != $totalPartsNumber) {
                $target[$pathPart] = array();
            }

            if ($currentPathNumber != $totalPartsNumber) {
                $target = &$target[$pathPart];
                continue;
            }

            $target[$pathPart] = $settingValue;
        }

        return $this->setSettings($fieldName, $settings, $encodeType);
    }

    //########################################
}
