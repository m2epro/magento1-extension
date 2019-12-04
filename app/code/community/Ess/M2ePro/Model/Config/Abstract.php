<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Config_Abstract extends Ess_M2ePro_Model_Abstract
{
    const SORT_NONE       = 0;
    const SORT_KEY_ASC    = 1;
    const SORT_KEY_DESC   = 2;
    const SORT_VALUE_ASC  = 3;
    const SORT_VALUE_DESC = 4;

    const CACHE_LIFETIME = 3600;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init($this->getModelName());
    }

    //########################################

    /**
     * @return self
     */
    protected function getModel()
    {
        return Mage::getModel($this->getModelName());
    }

    /**
     * @return string
     */
    abstract protected function getModelName();

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

    public function clear()
    {
        Mage::getSingleton('core/resource')->getConnection('core_write')->delete(
            $this->getResource()->getMainTable()
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

        return isset($cacheData[$group][$key]) ? $cacheData[$group][$key] : null;
    }

    protected function setValue($group, $key, $value)
    {
        if (empty($key) || empty($group)) {
            return false;
        }

        $collection = $this->getCollection();
        $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), $group);
        $collection->addFieldToFilter(new Zend_Db_Expr('`key`'), $key);

        $dbData = $collection->toArray();

        if (!empty($dbData['items'])) {
            $existItem = reset($dbData['items']);

            $this->getModel()
                ->load($existItem['id'])
                ->addData(array('value' => $value))
                ->save();
        } else {
            $this->getModel()
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

        $collection = $this->getCollection();
        $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), $group);
        $collection->addFieldToFilter(new Zend_Db_Expr('`key`'), $key);

        $dbData = $collection->toArray();

        if (empty($dbData['items'])) {
            return false;
        }

        $existItem = reset($dbData['items']);
        $this->getModel()->setId($existItem['id'])->delete();

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

        $collection = $this->getCollection();
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

        $collection = $this->getCollection();
        $collection->addFieldToFilter(new Zend_Db_Expr('`group`'), array('like' => $group));

        $dbData = $collection->toArray();

        foreach ($dbData['items'] as $item) {
            $this->getModel()->setId($item['id'])->delete();
        }

        $this->removeCacheData();

        return true;
    }

    //########################################

    protected function prepareGroup($group)
    {
        if (empty($group)) {
            return false;
        }

        return '/'.strtolower(trim($group, '/')).'/';
    }

    protected function prepareKey($key)
    {
        return strtolower($key);
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
        $key = $this->getModelName().'_data';
        return Mage::helper('M2ePro/Data_Cache_Permanent')->getValue($key);
    }

    protected function setCacheData(array $data)
    {
        $key = $this->getModelName().'_data';
        Mage::helper('M2ePro/Data_Cache_Permanent')->setValue($key, $data, array(), self::CACHE_LIFETIME);
    }

    protected function removeCacheData()
    {
        $key = $this->getModelName().'_data';
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeValue($key);
    }

    //########################################
}
