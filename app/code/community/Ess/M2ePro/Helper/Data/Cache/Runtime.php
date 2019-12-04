<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Data_Cache_Runtime extends Ess_M2ePro_Helper_Data_Cache_Abstract
{
    protected $_cacheStorage = array();

    //########################################

    public function getValue($key)
    {
        return !empty($this->_cacheStorage[$key]['data']) ? $this->_cacheStorage[$key]['data'] : null;
    }

    public function setValue($key, $value, array $tags = array(), $lifetime = null)
    {
        $this->_cacheStorage[$key] = array(
            'data' => $value,
            'tags' => $tags,
        );

        return $value;
    }

    //########################################

    public function removeValue($key)
    {
        if (!isset($this->_cacheStorage[$key])) {
            return false;
        }

        unset($this->_cacheStorage[$key]);
        return true;
    }

    public function removeTagValues($tag)
    {
        $isDelete = false;
        foreach ($this->_cacheStorage as $key => $data) {
            if (!in_array($tag, $data['tags'])) {
                continue;
            }

            unset($this->_cacheStorage[$key]);
            $isDelete = true;
        }

        return $isDelete;
    }

    public function removeAllValues()
    {
        if (empty($this->_cacheStorage)) {
            return false;
        }

        $this->_cacheStorage = array();
        return true;
    }

    //########################################
}
