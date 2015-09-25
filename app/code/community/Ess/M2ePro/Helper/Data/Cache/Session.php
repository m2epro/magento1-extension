<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Data_Cache_Session extends Ess_M2ePro_Helper_Data_Cache_Abstract
{
    // ##########################################################

    private $cacheStorage = array();

    // ##########################################################

    public function getValue($key)
    {
        return !empty($this->cacheStorage[$key]['data']) ? $this->cacheStorage[$key]['data'] : null;
    }

    public function setValue($key, $value, array $tags = array(), $lifetime = null)
    {
        $this->cacheStorage[$key] = array(
            'data' => $value,
            'tags'   => $tags,
        );

        return $value;
    }

    // ##########################################################

    public function removeValue($key)
    {
        if (!isset($this->cacheStorage[$key])) {
            return false;
        }

        unset($this->cacheStorage[$key]);
        return true;
    }

    public function removeTagValues($tag)
    {
        $isDelete = false;
        foreach ($this->cacheStorage as $key => $data) {
            if (!in_array($tag, $data['tags'])) {
                continue;
            }

            unset($this->cacheStorage[$key]);
            $isDelete = true;
        }

        return $isDelete;
    }

    public function removeAllValues()
    {
        if (empty($this->cacheStorage)) {
            return false;
        }

        $this->cacheStorage = array();
        return true;
    }

    // ##########################################################
}