<?php

class Ess_M2ePro_Model_Walmart_ProductType_AttributeSetting_Provider_Item
{
    /** @var string */
    private $name;
    /** @var string[] */
    private $values;

    /**
     * @param string $name
     * @param string[] $values
     */
    public function __construct($name, array $values)
    {
        $this->name = $name;
        $this->values = $values;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getValues()
    {
        return $this->values;
    }
}
