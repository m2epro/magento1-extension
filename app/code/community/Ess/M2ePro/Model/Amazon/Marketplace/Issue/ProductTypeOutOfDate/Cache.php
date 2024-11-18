<?php

class Ess_M2ePro_Model_Amazon_Marketplace_Issue_ProductTypeOutOfDate_Cache
{
    const CACHE_KEY = __CLASS__;

    /**
     * @var Ess_M2ePro_Helper_Data_Cache_Permanent
     */
    private $permanentCache;

    public function __construct()
    {
        $this->permanentCache = Mage::helper('M2ePro/Data_Cache_Permanent');
    }

    public function set($value)
    {
        $this->permanentCache->setValue(
            self::CACHE_KEY,
            $value,
            array('amazon', 'marketplace'),
            60 * 60
        );
    }

    public function get()
    {
        $value = $this->permanentCache->getValue(self::CACHE_KEY);
        if ($value === null) {
            return null;
        }

        return (bool)$value;
    }

    public function clear()
    {
        $this->permanentCache->removeValue(self::CACHE_KEY);
    }
}