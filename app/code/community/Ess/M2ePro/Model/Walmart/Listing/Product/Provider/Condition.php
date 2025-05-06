<?php

class Ess_M2ePro_Model_Walmart_Listing_Product_Provider_Condition
{
    /** @var string|null */
    public $value;

    /** @var bool */
    public $isNotFoundMagentoAttribute = false;

    /**
     * @param string $value
     * @return self
     */
    public static function createWithValue($value)
    {
        return new self($value);
    }

    /**
     * @return self
     */
    public static function createWithoutMagentoAttribute()
    {
        $condition = new self();
        $condition->isNotFoundMagentoAttribute = true;

        return $condition;
    }

    private function __construct($value = null)
    {
        $this->value = $value;
    }
}
