<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Modifier_Config_Entity
{
    protected $_group = null;
    protected $_key   = null;

    /**
     * @var Ess_M2ePro_Model_Upgrade_Modifier_Config
     */
    protected $_configModifier = null;

    //########################################

    public function setGroup($group)
    {
        $this->_group = $group;
        return $this;
    }

    public function setKey($key)
    {
        $this->_key = $key;
        return $this;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Upgrade_Modifier_Config $configModifier
     * @return $this
     */
    public function setConfigModifier(Ess_M2ePro_Model_Upgrade_Modifier_Config $configModifier)
    {
        $this->_configModifier = $configModifier;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Upgrade_Modifier_Config
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function getConfigModifier()
    {
        if ($this->_configModifier === null) {
            throw new Ess_M2ePro_Model_Exception_Setup("ConfigModifier does not exist.");
        }

        return $this->_configModifier;
    }

    //########################################

    public function isExists()
    {
        return $this->getConfigModifier()->isExists($this->_group, $this->_key);
    }

    // ---------------------------------------

    public function getGroup()
    {
        return $this->_group;
    }

    public function getKey()
    {
        return $this->_key;
    }

    public function getValue()
    {
        $row = $this->getConfigModifier()->getRow($this->_group, $this->_key);
        return isset($row['value']) ? $row['value'] : NULL;
    }

    // ---------------------------------------

    public function insert($value)
    {
        $result = $this->getConfigModifier()->insert($this->_group, $this->_key, $value);

        if ($result instanceof Ess_M2ePro_Model_Upgrade_Modifier_Config) {
            return $this;
        }

        return $result;
    }

    public function updateGroup($value)
    {
        return $this->getConfigModifier()->updateGroup(
            $value, array('`group` = ?' => $this->_group, '`key` = ?' => $this->_key)
        );
    }

    public function updateKey($value)
    {
        return $this->getConfigModifier()->updateKey(
            $value, array('`group` = ?' => $this->_group, '`key` = ?' => $this->_key)
        );
    }

    public function updateValue($value)
    {
        return $this->getConfigModifier()->updateValue(
            $value, array('`group` = ?' => $this->_group, '`key` = ?' => $this->_key)
        );
    }

    public function delete()
    {
        $result = $this->getConfigModifier()->delete($this->_group, $this->_key);

        if ($result instanceof Ess_M2ePro_Model_Upgrade_Modifier_Config) {
            return $this;
        }

        return $result;
    }

    //########################################
}
