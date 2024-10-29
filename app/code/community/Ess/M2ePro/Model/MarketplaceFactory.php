<?php

class Ess_M2ePro_Model_MarketplaceFactory
{
    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function createEmpty()
    {
        /** @var Ess_M2ePro_Model_Marketplace */
        return Mage::getModel('M2ePro/Marketplace');
    }
}
