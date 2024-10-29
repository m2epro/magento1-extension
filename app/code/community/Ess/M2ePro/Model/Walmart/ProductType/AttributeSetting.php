<?php

class Ess_M2ePro_Model_Walmart_ProductType_AttributeSetting
{
    /** @var string */
    private $attributeName;
    private $values = array();

    /**
     * @param string $attributeName
     */
    public function __construct($attributeName)
    {
        $this->attributeName = $attributeName;
    }

    /**
     * @return void
     */
    public function addValue(Ess_M2ePro_Model_Walmart_ProductType_AttributeSetting_Value $value)
    {
        $this->values[] = $value;
    }

    /**
     * @return string
     */
    public function getAttributeName()
    {
        return $this->attributeName;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_ProductType_AttributeSetting_Value[]
     */
    public function getValues()
    {
        return $this->values;
    }
}