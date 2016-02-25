<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Config_Abstract extends Ess_M2ePro_Model_Abstract
{
    const SORT_NONE       = 0;
    const SORT_KEY_ASC    = 1;
    const SORT_KEY_DESC   = 2;
    const SORT_VALUE_ASC  = 3;
    const SORT_VALUE_DESC = 4;

    const GLOBAL_GROUP = '__global__';

    const CACHE_LIFETIME = 3600; // 1 hour

    //########################################

    private $_ormConfig = '';

    //########################################

    public function __construct()
    {
        $args = func_get_args();
        empty($args[0]) && $args[0] = array();
        $params = $args[0];

        if (empty($params['orm'])) {
            throw new Ess_M2ePro_Model_Exception('ORM for config is not defined.');
        }

        $this->_ormConfig = $params['orm'];
        parent::__construct();
    }

    //########################################

    public function getGlobalValue($key)
    {
        return $this->getValue(self::GLOBAL_GROUP, $this->prepareKey($key));
    }

    public function setGlobalValue($key, $value)
    {
        return $this->setValue(self::GLOBAL_GROUP, $this->prepareKey($key), $value);
    }

    public function deleteGlobalValue($key)
    {
        return $this->deleteValue(self::GLOBAL_GROUP, $this->prepareKey($key));
    }

    // ---------------------------------------

    public function getAllGlobalValues($sort = self::SORT_NONE)
    {
        return $this->getAllValues(self::GLOBAL_GROUP, $sort);
    }

    public function deleteAllGlobalValues()
    {
        return $this->deleteAllValues(self::GLOBAL_GROUP);
    }

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
        return $this->getAllValues($this->prepareGroup($group),$sort);
    }

    public function deleteAllGroupValues($group)
    {
        return $this->deleteAllValues($this->prepareGroup($group));
    }

    // ---------------------------------------

    public function clear()
    {
        Mage::getSingleton('core/resource')->getConnection('core_write')->delete(
            $this->getResource()->getMainTable()
        );

        $this->removeCacheData();
    }

    //########################################

    private function getValue($group, $key)
    {
        if (empty($group) || empty($key)) {
            return NULL;
        }

        $cacheData = $this->getCacheData();

        if (!empty($cacheData)) {
            return isset($cacheData[$group][$key]) ? $cacheData[$group][$key] : NULL;
        }

        $dbData = $this->getCollection()->toArray();

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

        return isset($cacheData[$group][$key]) ? $cacheData[$group][$key] : NULL;
    }

    private function setValue($group, $key, $value)
    {
        if (empty($key) || empty($group)) {
            return false;
        }

        $collection = $this->getCollection();

        if ($group == self::GLOBAL_GROUP) {
            $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), array('null' => true));
        } else {
            $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), $group);
        }

        $collection->addFieldToFilter(new Zend_Db_Expr('`key`'), $key);
        $dbData = $collection->toArray();

        if (count($dbData['items']) > 0) {

            $existItem = reset($dbData['items']);

            Mage::getModel($this->_ormConfig)
                         ->load($existItem['id'])
                         ->addData(array('value' => $value))
                         ->save();
        } else {

            Mage::getModel($this->_ormConfig)
                         ->setData(array('group' => $group,'key' => $key,'value' => $value))
                         ->save();
        }

        $this->removeCacheData();

        return true;
    }

    private function deleteValue($group, $key)
    {
        if (empty($key) || empty($group)) {
            return false;
        }

        $collection = $this->getCollection();

        if ($group == self::GLOBAL_GROUP) {
            $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), array('null' => true));
        } else {
            $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), $group);
        }

        $collection->addFieldToFilter(new Zend_Db_Expr('`key`'), $key);
        $dbData = $collection->toArray();

        if (empty($dbData['items'])) {
            return false;
        }

        $existItem = reset($dbData['items']);
        Mage::getModel($this->_ormConfig)->setId($existItem['id'])->delete();

        $this->removeCacheData();

        return true;
    }

    // ---------------------------------------

    private function getAllValues($group = NULL, $sort = self::SORT_NONE)
    {
        if (empty($group)) {
            return array();
        }

        $result = array();

        $collection = $this->getCollection();

        if ($group == self::GLOBAL_GROUP) {
            $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), array('null' => true));
        } else {
            $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), $group);
        }

        $dbData = $collection->toArray();

        foreach ($dbData['items'] as $item) {
            $result[$item['key']] = $item['value'];
        }

        $this->sortResult($result, $sort);

        return $result;
    }

    private function deleteAllValues($group = NULL)
    {
        if (empty($group)) {
            return false;
        }

        $collection = $this->getCollection();

        if ($group == self::GLOBAL_GROUP) {
            $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), array('null' => true));
        } else {
            $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), array('like' => $group.'%'));
        }

        $dbData = $collection->toArray();

        foreach ($dbData['items'] as $item) {
            Mage::getModel($this->_ormConfig)->setId($item['id'])->delete();
        }

        $this->removeCacheData();

        return true;
    }

    //########################################

    private function prepareGroup($group)
    {
        if (is_null($group) || $group == self::GLOBAL_GROUP) {
            return self::GLOBAL_GROUP;
        }

        if (empty($group)) {
            return false;
        }

        return '/'.strtolower(trim($group,'/')).'/';
    }

    private function prepareKey($key)
    {
        return strtolower($key);
    }

    //########################################

    private function sortResult(&$array, $sort)
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

    private function getCacheData()
    {
        $key = $this->_ormConfig.'_data';
        return Mage::helper('M2ePro/Data_Cache_Permanent')->getValue($key);
    }

    private function setCacheData(array $data)
    {
        $key = $this->_ormConfig.'_data';
        Mage::helper('M2ePro/Data_Cache_Permanent')->setValue($key, $data, array(), self::CACHE_LIFETIME);
    }

    private function removeCacheData()
    {
        $key = $this->_ormConfig.'_data';
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeValue($key);
    }

    //########################################
}