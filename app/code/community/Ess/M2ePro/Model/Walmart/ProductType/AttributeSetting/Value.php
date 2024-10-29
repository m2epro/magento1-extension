<?php

class Ess_M2ePro_Model_Walmart_ProductType_AttributeSetting_Value
{
    const TYPE_PRODUCT_ATTRIBUTE_CODE = 'product_attribute_code';
    const TYPE_CUSTOM = 'custom';

    /** @var string */
    private $type;
    /** @var string */
    private $value;

    /**
     * @param string $value
     * @return self
     */
    public static function createAsProductAttributeCode($value)
    {
        return new self(self::TYPE_PRODUCT_ATTRIBUTE_CODE, $value);
    }

    /**
     * @param string $value
     * @return self
     */
    public static function createAsCustom($value)
    {
        return new self(self::TYPE_CUSTOM, $value);
    }

    /**
     * @param string $type
     * @param string $value
     */
    private function __construct($type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @return bool
     */
    public function isProductAttributeCode()
    {
        return $this->type === self::TYPE_PRODUCT_ATTRIBUTE_CODE;
    }

    /**
     * @return bool
     */
    public function isCustom()
    {
        return $this->type === self::TYPE_CUSTOM;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}