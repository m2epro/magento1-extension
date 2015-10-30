<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Modifier_Config_Entity
{
    private $group = NULL;
    private $key = NULL;

    /**
     * @var Ess_M2ePro_Model_Upgrade_Modifier_Config
     */
    private $configModifier = NULL;

    //########################################

    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Upgrade_Modifier_Config $configModifier
     * @return $this
     */
    public function setConfigModifier(Ess_M2ePro_Model_Upgrade_Modifier_Config $configModifier)
    {
        $this->configModifier = $configModifier;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Upgrade_Modifier_Config
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function getConfigModifier()
    {
        if (is_null($this->configModifier)) {
            throw new Ess_M2ePro_Model_Exception_Setup("ConfigModifier does not exist.");
        }

        return $this->configModifier;
    }

    //########################################

    public function isExists()
    {
        return $this->getConfigModifier()->isExists($this->group, $this->key);
    }

    // ---------------------------------------

    public function getGroup()
    {
        return $this->group;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        $row = $this->getConfigModifier()->getRow($this->group, $this->key);
        return isset($row['value']) ? $row['value'] : NULL;
    }

    // ---------------------------------------

    public function insert($value)
    {
        $result = $this->getConfigModifier()->insert($this->group, $this->key, $value);

        if ($result instanceof Ess_M2ePro_Model_Upgrade_Modifier_Config) {
            return $this;
        }

        return $result;
    }

    public function updateGroup($value)
    {
        return $this->getConfigModifier()->updateGroup(
            $value, array('`group` = ?' => $this->group, '`key` = ?' => $this->key)
        );
    }

    public function updateKey($value)
    {
        return $this->getConfigModifier()->updateKey(
            $value, array('`group` = ?' => $this->group, '`key` = ?' => $this->key)
        );
    }

    public function updateValue($value)
    {
        return $this->getConfigModifier()->updateValue(
            $value, array('`group` = ?' => $this->group, '`key` = ?' => $this->key)
        );
    }

    public function delete()
    {
        $result = $this->getConfigModifier()->delete($this->group, $this->key);

        if ($result instanceof Ess_M2ePro_Model_Upgrade_Modifier_Config) {
            return $this;
        }

        return $result;
    }

    //########################################
}