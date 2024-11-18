<?php

class Ess_M2ePro_Model_Resource_Marketplace_CollectionFactory
{
    /**
     * @return Ess_M2ePro_Model_Resource_Marketplace_Collection
     */
    public function createWithWalmartChildMode()
    {
        return $this->createWithChildMode(Ess_M2ePro_Helper_Component_Walmart::NICK);
    }

    /**
     * @return Ess_M2ePro_Model_Resource_Marketplace_Collection
     */
    public function createWithAmazonChildMode()
    {
        return $this->createWithChildMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
    }

    /**
     * @param string $childMode
     *
     * @return Ess_M2ePro_Model_Resource_Marketplace_Collection
     */
    private function createWithChildMode($childMode)
    {
        /** @var Ess_M2ePro_Model_Resource_Marketplace $marketplaceResource */
        $marketplaceResource = Mage::getResourceModel('M2ePro/Marketplace');
        $marketplaceResource->setChildMode($childMode);

        /** @var Ess_M2ePro_Model_Resource_Marketplace_Collection */
        return Mage::getResourceModel('M2ePro/Marketplace_Collection', $marketplaceResource);
    }
}