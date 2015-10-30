<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Data_Cache_Permanent extends Ess_M2ePro_Helper_Data_Cache_Abstract
{
    //########################################

    public function getValue($key)
    {
        $cacheKey = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_'.$key;
        $value = Mage::app()->getCache()->load($cacheKey);
        $value !== false && $value = unserialize($value);
        return $value;
    }

    public function setValue($key, $value, array $tags = array(), $lifeTime = NULL)
    {
        if (is_null($lifeTime) || (int)$lifeTime <= 0) {
            $lifeTime = 60*60*24*365*5;
        }

        $cacheKey = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_'.$key;

        $preparedTags = array(Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_main');
        foreach ($tags as $tag) {
            $preparedTags[] = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_'.$tag;
        }

        Mage::app()->getCache()->save(serialize($value), $cacheKey, $preparedTags, (int)$lifeTime);
    }

    //########################################

    public function removeValue($key)
    {
        $cacheKey = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_'.$key;
        Mage::app()->getCache()->remove($cacheKey);
    }

    public function removeTagValues($tag)
    {
        $mode = Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG;
        $tags = array(Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER.'_'.$tag);
        Mage::app()->getCache()->clean($mode,$tags);
    }

    public function removeAllValues()
    {
        $this->removeTagValues('main');
    }

    //########################################
}