<?php

class Ess_M2ePro_Helper_Component_Amazon_Configuration extends Mage_Core_Helper_Abstract
{
    const CONFIG_GROUP = '/amazon/configuration/';

    const GENERAL_ID_MODE_NONE = 0;
    const GENERAL_ID_MODE_CUSTOM_ATTRIBUTE = 1;
    const WORLDWIDE_ID_MODE_NONE = 0;
    const WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE = 1;

    /** @var Ess_M2ePro_Model_Config_Manager */
    private $config;

    public function __construct()
    {
        $this->config = Mage::helper('M2ePro/Module')->getConfig();
    }

    // ----------------------------------------

    /**
     * @return int
     */
    public function getBusinessMode()
    {
        return (int)$this->config->getGroupValue(
            self::CONFIG_GROUP,
            'business_mode'
        );
    }

    /**
     * @return bool
     */
    public function isEnabledBusinessMode()
    {
        return $this->getBusinessMode() == 1;
    }

    // ----------------------------------------

    /**
     * @return int
     */
    public function getWorldwideIdMode()
    {
        return (int)$this->config->getGroupValue(
            self::CONFIG_GROUP,
            'worldwide_id_mode'
        );
    }

    /**
     * @return bool
     */
    public function isWorldwideIdModeNone()
    {
        return $this->getWorldwideIdMode() == self::WORLDWIDE_ID_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isWorldwideIdModeCustomAttribute()
    {
        return $this->getWorldwideIdMode() == self::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return string|null
     */
    public function getWorldwideCustomAttribute()
    {
        return $this->config->getGroupValue(
            self::CONFIG_GROUP,
            'worldwide_id_custom_attribute'
        );
    }

    // ----------------------------------------

    /**
     * @return int
     */
    public function getGeneralIdMode()
    {
        return (int)$this->config->getGroupValue(
            self::CONFIG_GROUP,
            'general_id_mode'
        );
    }

    /**
     * @return bool
     */
    public function isGeneralIdModeNone()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isGeneralIdModeCustomAttribute()
    {
        return $this->getGeneralIdMode() == self::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return string|null
     */
    public function getGeneralIdCustomAttribute()
    {
        return $this->config->getGroupValue(
            self::CONFIG_GROUP,
            'general_id_custom_attribute'
        );
    }

    // ----------------------------------------

    public function setConfigValues(array $values)
    {
        $allowedConfigKeys = array(
            'business_mode',
            'worldwide_id_mode',
            'worldwide_id_custom_attribute',
            'general_id_mode',
            'general_id_custom_attribute',
        );

        foreach ($allowedConfigKeys as $configKey) {
            if (!isset($values[$configKey])) {
                continue;
            }

            $this->config->setGroupValue(
                self::CONFIG_GROUP,
                $configKey,
                $values[$configKey]
            );
        }
    }
}
