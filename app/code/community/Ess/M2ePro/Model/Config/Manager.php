<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Config_Manager
{
    const SORT_NONE       = 0;
    const SORT_KEY_ASC    = 1;
    const SORT_KEY_DESC   = 2;
    const SORT_VALUE_ASC  = 3;
    const SORT_VALUE_DESC = 4;

    const CACHE_LIFETIME = 3600;

    //########################################

    public function getGroupValue($group, $key)
    {
        return $this->getValue($this->prepareGroup($group), $this->prepareKey($key));
    }

    public function setGroupValue($group, $key, $value)
    {
        return $this->setValue($this->prepareGroup($group), $this->prepareKey($key), $value);
    }

    public function deleteGroupValue($group, $key)
    {
        return $this->deleteValue($this->prepareGroup($group), $this->prepareKey($key));
    }

    // ---------------------------------------

    public function getAllGroupValues($group, $sort = self::SORT_NONE)
    {
        return $this->getAllValues($this->prepareGroup($group), $sort);
    }

    public function deleteAllGroupValues($group)
    {
        return $this->deleteAllValues($this->prepareGroup($group));
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getAllConfigData()
    {
        $collection = Mage::getModel('M2ePro/Config')->getCollection()->toArray();
        return $collection['items'];
    }

    // ---------------------------------------

    public function clear()
    {
        Mage::getSingleton('core/resource')->getConnection('core_write')->delete(
            Mage::getModel('M2ePro/Config')->getResource()->getMainTable()
        );

        $this->removeCacheData();
    }

    //########################################

    protected function getValue($group, $key)
    {
        if (empty($group) || empty($key)) {
            return null;
        }

        $cacheData = $this->getCacheData();

        if (!empty($cacheData)) {
            return isset($cacheData[$group][$key]) ? $cacheData[$group][$key] : null;
        }

        $dbData = Mage::getModel('M2ePro/Config')->getCollection()->toArray();

        $cacheData = array();
        foreach ($dbData['items'] as $item) {
            $item['group'] = $this->prepareGroup($item['group']);
            $item['key']   = $this->prepareKey($item['key']);

            if (!isset($cacheData[$item['group']])) {
                $cacheData[$item['group']] = array();
            }

            $cacheData[$item['group']][$item['key']] = $item['value'];
        }

        $this->setCacheData($cacheData);

        return isset($cacheData[$group][$key]) ? $cacheData[$group][$key] : null;
    }

    protected function setValue($group, $key, $value)
    {
        if (empty($key) || empty($group)) {
            return false;
        }

        $collection = Mage::getModel('M2ePro/Config')->getCollection();
        $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), $group);
        $collection->addFieldToFilter(new Zend_Db_Expr('`key`'), $key);

        $dbData = $collection->toArray();

        if (!empty($dbData['items'])) {
            $existItem = reset($dbData['items']);

            Mage::getModel('M2ePro/Config')
                ->load($existItem['id'])
                ->addData(array('value' => $value))
                ->save();
        } else {
            Mage::getModel('M2ePro/Config')
                ->setData(array('group' => $group, 'key' => $key, 'value' => $value))
                ->save();
        }

        $this->removeCacheData();

        return true;
    }

    protected function deleteValue($group, $key)
    {
        if (empty($key) || empty($group)) {
            return false;
        }

        $collection = Mage::getModel('M2ePro/Config')->getCollection();
        $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), $group);
        $collection->addFieldToFilter(new Zend_Db_Expr('`key`'), $key);

        $dbData = $collection->toArray();

        if (empty($dbData['items'])) {
            return false;
        }

        $existItem = reset($dbData['items']);
        Mage::getModel('M2ePro/Config')->setId($existItem['id'])->delete();

        $this->removeCacheData();

        return true;
    }

    // ---------------------------------------

    protected function getAllValues($group, $sort = self::SORT_NONE)
    {
        if (empty($group)) {
            return array();
        }

        $result = array();

        $collection = Mage::getModel('M2ePro/Config')->getCollection();
        $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), $group);

        $dbData = $collection->toArray();

        foreach ($dbData['items'] as $item) {
            $result[$item['key']] = $item['value'];
        }

        $this->sortResult($result, $sort);

        return $result;
    }

    protected function deleteAllValues($group)
    {
        if (empty($group)) {
            return false;
        }

        $collection = Mage::getModel('M2ePro/Config')->getCollection();
        $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), array('like' => $group));

        $dbData = $collection->toArray();

        foreach ($dbData['items'] as $item) {
            Mage::getModel('M2ePro/Config')->setId($item['id'])->delete();
        }

        $this->removeCacheData();

        return true;
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function prepareGroup($group)
    {
        if (empty($group)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Configuration group cannot be empty.');
        }

        $group = trim($group);
        if ($group === '/') {
            return $group;
        }

        return '/'.strtolower(trim($group, '/')).'/';
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function prepareKey($key)
    {
        if (empty($key)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Configuration key cannot be empty.');
        }

        return strtolower(trim($key));
    }

    //########################################

    protected function sortResult(&$array, $sort)
    {
        switch ($sort)
        {
            case self::SORT_KEY_ASC:
                ksort($array);
                break;

            case self::SORT_KEY_DESC:
                krsort($array);
                break;

            case self::SORT_VALUE_ASC:
                asort($array);
                break;

            case self::SORT_VALUE_DESC:
                arsort($array);
                break;
        }
    }

    //########################################

    protected function getCacheData()
    {
        return Mage::helper('M2ePro/Data_Cache_Permanent')->getValue('m2ePro_config_data');
    }

    protected function setCacheData(array $data)
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->setValue(
            'm2ePro_config_data',
            $data,
            array(),
            self::CACHE_LIFETIME
        );
    }

    protected function removeCacheData()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeValue('m2ePro_config_data');
    }

    //########################################
}
