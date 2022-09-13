<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Modifier_Config extends Ess_M2ePro_Model_Upgrade_Modifier_Abstract
{
    //########################################

    /**
     * @param string $group
     * @param string $key
     * @return mixed
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function getRow($group, $key)
    {
        $group = $this->prepareGroup($group);
        $key = $this->prepareKey($key);

        $query = $this->getConnection()
                      ->select()
                      ->from($this->getTableName())
                      ->where('`key` = ?', $key);

        if ($group === null) {
            $query->where('`group` IS NULL');
        } else {
            $query->where('`group` = ?', $group);
        }

        return $this->getConnection()->fetchRow($query);
    }

    /**
     * @param string $group
     * @param string $key
     * @return Ess_M2ePro_Model_Upgrade_Modifier_Config_Entity
     */
    public function getEntity($group, $key)
    {
        $entity = Mage::getModel('M2ePro/Upgrade_Modifier_Config_Entity');
        $entity->setGroup($this->prepareGroup($group))
               ->setKey($this->prepareKey($key))
               ->setConfigModifier($this);
        return $entity;
    }

    //########################################

    /**
     * @param string $group
     * @param string|null $key
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function isExists($group, $key = null)
    {
        $query = $this->getConnection()
                      ->select()
                      ->from($this->getTableName());

        $group = $this->prepareGroup($group);
        if ($group === null) {
            $query->where('`group` IS NULL');
        } else {
            $query->where('`group` = ?', $group);
        }

        if ($key !== null) {
            $query->where('`key` = ?', $this->prepareKey($key));
        }

        $row = $this->getConnection()->fetchOne($query);
        return !empty($row);
    }

    // ---------------------------------------

    /**
     * @param string $group
     * @param string $key
     * @param string|null $value
     * @param null $notice is not supported. left for backward compatibility
     * @return $this|int
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function insert($group, $key, $value = null, $notice = null)
    {
        if ($this->isExists($group, $key)) {
            return $this;
        }

        $preparedData = array(
            'group' => $this->prepareGroup($group),
            'key'   => $this->prepareKey($key),
        );

        $value !== null && $preparedData['value'] = $value;

        $preparedData['update_date'] = $this->getCurrentDateTime();
        $preparedData['create_date'] = $this->getCurrentDateTime();

        return $this->getConnection()->insert($this->getTableName(), $preparedData);
    }

    /**
     * @param string $field
     * @param string $value
     * @param string|array $where
     * @return int
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Adapter_Exception
     */
    public function update($field, $value, $where)
    {
        $field == 'group' && $value = $this->prepareGroup($value);
        $field == 'key'   && $value = $this->prepareKey($value);

        $preparedData = array(
            $field        => $value,
            'update_date' => $this->getCurrentDateTime()
        );

        return $this->getConnection()->update($this->getTableName(), $preparedData, $where);
    }

    /**
     * @param string $group
     * @param string|null $key
     * @return $this|int
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function delete($group, $key = null)
    {
        if (!$this->isExists($group, $key)) {
            return $this;
        }

        $group = $this->prepareGroup($group);
        if ($group === null) {
            $where = array('`group` IS NULL');
        } else {
            $where = array('`group` = ?' => $group);
        }

        if ($key !== null) {
            $where['`key` = ?'] = $this->prepareKey($key);
        }

        return $this->getConnection()->delete($this->getTableName(), $where);
    }

    //########################################

    /**
     * @param string $value
     * @param string|array $where
     * @return int
     */
    public function updateGroup($value, $where)
    {
        return $this->update('group', $value, $where);
    }

    /**
     * @param string $value
     * @param string|array $where
     * @return int
     */
    public function updateKey($value, $where)
    {
        return $this->update('key', $value, $where);
    }

    /**
     * @param string $value
     * @param string|array $where
     * @return int
     */
    public function updateValue($value, $where)
    {
        return $this->update('value', $value, $where);
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Statement_Exception
     */
    public function removeDuplicates()
    {
        $tempData = array();
        $deleteData = array();

        $configRows = $this->getConnection()
                        ->query(
                            "SELECT `id`, `group`, `key`
                                    FROM `{$this->getTableName()}`
                                    ORDER BY `id` ASC"
                        )
                           ->fetchAll();

        foreach ($configRows as $configRow) {
            $tempName = strtolower($configRow['group'] .'|'. $configRow['key']);

            if (in_array($tempName, $tempData)) {
                $deleteData[] = (int)$configRow['id'];
            } else {
                $tempData[] = $tempName;
            }
        }

        if (!empty($deleteData)) {
            $this->getConnection()
                ->query(
                    "DELETE FROM `{$this->getTableName()}`
                          WHERE `id` IN (".implode(',', $deleteData).')'
                );
        }
    }

    //########################################

    private function prepareGroup($group)
    {
        if ($group === null || $group === '/') {
            return $group;
        }

        return '/' . trim($group, '/') . '/';
    }

    private function prepareKey($key)
    {
        if ($key === null) {
            return $key;
        }

        return strtolower($key);
    }

    //########################################

    protected function getCurrentDateTime()
    {
        return date('Y-m-d H:i:s', gmdate('U'));
    }

    //########################################
}
