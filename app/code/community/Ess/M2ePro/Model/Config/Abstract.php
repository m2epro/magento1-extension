<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Config_Abstract extends Ess_M2ePro_Model_Abstract
{
    const SORT_NONE = 0;
    const SORT_KEY_ASC = 1;
    const SORT_KEY_DESC = 2;
    const SORT_VALUE_ASC = 3;
    const SORT_VALUE_DESC = 4;

    //########################################

    private $_ormConfig = '';
    private $_cacheData = array();

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
        return $this->getValue(NULL, $key);
    }

    public function setGlobalValue($key, $value)
    {
        return $this->setValue(NULL, $key, $value);
    }

    public function deleteGlobalValue($key)
    {
        return $this->deleteValue(NULL, $key);
    }

    // ---------------------------------------

    public function getAllGlobalValues($sort = self::SORT_NONE)
    {
        return $this->getAllValues(NULL,$sort);
    }

    public function deleteAllGlobalValues()
    {
        return $this->deleteAllValues(NULL);
    }

    //########################################

    public function getGroupValue($group, $key)
    {
        $group = $this->prepareGroup($group);
        return $this->getValue($group, $key);
    }

    public function setGroupValue($group, $key, $value)
    {
        $group = $this->prepareGroup($group);
        return $this->setValue($group, $key, $value);
    }

    public function deleteGroupValue($group, $key)
    {
        $group = $this->prepareGroup($group);
        return $this->deleteValue($group, $key);
    }

    // ---------------------------------------

    public function getAllGroupValues($group, $sort = self::SORT_NONE)
    {
        $group = $this->prepareGroup($group);
        return $this->getAllValues($group,$sort);
    }

    public function deleteAllGroupValues($group)
    {
        $group = $this->prepareGroup($group);
        return $this->deleteAllValues($group);
    }

    // ---------------------------------------

    public function clear()
    {
        $tableName = $this->getResource()->getMainTable();
        Mage::getSingleton('core/resource')->getConnection('core_write')->delete($tableName);

        $this->_cacheData = array();
        $this->updatePermanentCacheData();
    }

    //########################################

    private function getValue($group, $key)
    {
        $this->loadCacheData();

        if (!is_null($group) && empty($group)) {
            return NULL;
        }

        if (empty($key)) {
            return NULL;
        }

        return $this->getCacheValue($group, $key);
    }

    private function setValue($group, $key, $value)
    {
        $this->loadCacheData();

        if (!is_null($group) && empty($group)) {
            return false;
        }

        if (empty($key)) {
            return false;
        }

        $temp = $this->getCollection();

        if (is_null($group)) {
            $temp->addFieldToFilter(new Zend_Db_Expr('`group`'), array('null' => true));
        } else {
            $temp->addFieldToFilter(new Zend_Db_Expr('`group`'), $group);
        }

        $temp->addFieldToFilter(new Zend_Db_Expr('`key`'), $key);
        $temp = $temp->toArray();

        if (count($temp['items']) > 0) {

            $existItem = $temp['items'][0];

            Mage::getModel($this->_ormConfig)
                         ->load($existItem['id'])
                         ->addData(array('value'=>$value))
                         ->save();
        } else {

            Mage::getModel($this->_ormConfig)
                         ->setData(array('group'=>$group,'key'=>$key,'value'=>$value))
                         ->save();
        }

        return $this->setCacheValue($group,$key,$value);
    }

    private function deleteValue($group, $key)
    {
        $this->loadCacheData();

        if (!is_null($group) && empty($group)) {
            return false;
        }

        if (empty($key)) {
            return false;
        }

        $temp = $this->getCollection();

        if (is_null($group)) {
            $temp->addFieldToFilter(new Zend_Db_Expr('`group`'), array('null' => true));
        } else {
            $temp->addFieldToFilter(new Zend_Db_Expr('`group`'), $group);
        }

        $temp->addFieldToFilter(new Zend_Db_Expr('`key`'), $key);
        $temp = $temp->toArray();

        if (count($temp['items']) <= 0) {
            return false;
        }

        $existItem = $temp['items'][0];
        Mage::getModel($this->_ormConfig)->setId($existItem['id'])->delete();

        return $this->deleteCacheValue($existItem['group'], $existItem['key']);
    }

    // ---------------------------------------

    private function getAllValues($group = NULL, $sort = self::SORT_NONE)
    {
        $this->loadCacheData();

        if (!is_null($group) && empty($group)) {
            return array();
        }

        $result = array();

        $temp = $this->getCollection();

        if (is_null($group)) {
            $temp->addFieldToFilter(new Zend_Db_Expr('`group`'), array('null' => true));
        } else {
            $temp->addFieldToFilter(new Zend_Db_Expr('`group`'), $group);
        }

        $temp = $temp->toArray();

        foreach ($temp['items'] as $item) {
            $result[$item['key']] = $item['value'];
        }

        $this->sortResult($result,$sort);

        return $result;
    }

    private function deleteAllValues($group = NULL)
    {
        $this->loadCacheData();

        if (!is_null($group) && empty($group)) {
            return false;
        }

        $temp = $this->getCollection();

        if (is_null($group)) {
            $temp->addFieldToFilter(new Zend_Db_Expr('`group`'), array('null' => true));
        } else {
            $temp->addFieldToFilter(new Zend_Db_Expr('`group`'), array("like"=>$group.'%'));
        }

        $temp = $temp->toArray();

        foreach ($temp['items'] as $item) {
            Mage::getModel($this->_ormConfig)->setId($item['id'])->delete();
            $this->deleteCacheValue($item['group'], $item['key']);
        }

        return true;
    }

    //########################################

    private function prepareGroup($group = NULL)
    {
        if (is_null($group)) {
            return NULL;
        }

        if (empty($group)) {
            return false;
        }

        return '/'.trim($group,'/').'/';
    }

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

    // ---------------------------------------

    private function getCacheValue($group = NULL, $key)
    {
        empty($group) && $group = 'global';

        if (empty($key)) {
            return NULL;
        }

        $group = strtolower($group);
        $key = strtolower($key);

        if (isset($this->_cacheData[$group][$key])) {
            return $this->_cacheData[$group][$key];
        }

        return NULL;
    }

    private function setCacheValue($group = NULL, $key, $value)
    {
        empty($group) && $group = 'global';

        if (empty($key)) {
            return false;
        }

        $group = strtolower($group);
        $key = strtolower($key);

        if (!isset($this->_cacheData[$group])) {
            $this->_cacheData[$group] = array();
        }

        $this->_cacheData[$group][$key] = $value;
        $this->updatePermanentCacheData();

        return true;
    }

    private function deleteCacheValue($group = NULL, $key)
    {
        empty($group) && $group = 'global';

        if (empty($key)) {
            return false;
        }

        $group = strtolower($group);
        $key = strtolower($key);

        unset($this->_cacheData[$group][$key]);
        $this->updatePermanentCacheData();

        return true;
    }

    // ---------------------------------------

    private function loadCacheData()
    {
        if (!empty($this->_cacheData)) {
            return;
        }

        $key = $this->_ormConfig.'_data';
        $this->_cacheData = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue($key);

        if ($this->_cacheData === false || Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {
            $this->_cacheData = $this->buildCacheData();
            $this->updatePermanentCacheData();
        }
    }

    private function buildCacheData()
    {
        $tempData = $this->getCollection()->toArray();

        $newCache = array();
        foreach ($tempData['items'] as $item) {

            if (empty($item['group'])) {
                $item['group'] = 'global';
            }

            $item['group'] = strtolower($item['group']);
            $item['key'] = strtolower($item['key']);

            if (!isset($newCache[$item['group']])) {
                $newCache[$item['group']] = array();
            }

            $newCache[$item['group']][$item['key']] = $item['value'];
        }

        return $newCache;
    }

    private function updatePermanentCacheData()
    {
        $key = $this->_ormConfig.'_data';
        Mage::helper('M2ePro/Data_Cache_Permanent')->setValue($key,$this->_cacheData,array(),60*60);
    }

    //########################################
}