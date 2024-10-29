<?php

class Ess_M2ePro_Model_Walmart_ProductTypeFactory
{
    /**
     * @return Ess_M2ePro_Model_Walmart_ProductType
     */
    public function create()
    {
        return $this->createEmpty();
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_ProductType
     */
    public function createEmpty()
    {
        /** @var Ess_M2ePro_Model_Walmart_ProductType */
        return Mage::getModel('M2ePro/Walmart_ProductType');
    }
}