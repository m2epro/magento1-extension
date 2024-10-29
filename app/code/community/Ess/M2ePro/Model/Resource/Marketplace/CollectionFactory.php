<?php

class Ess_M2ePro_Model_Resource_Marketplace_CollectionFactory
{
    /**
     * @return Ess_M2ePro_Model_Resource_Marketplace_Collection
     */
    public function createWithWalmartChildMode()
    {
        /** @var Ess_M2ePro_Model_Resource_Marketplace $marketplaceResource */
        $marketplaceResource = Mage::getResourceModel('M2ePro/Marketplace');
        $marketplaceResource->setChildMode(Ess_M2ePro_Helper_Component_Walmart::NICK);

        return $this->createWithChildMode($marketplaceResource);
    }

    /**
     * @param Ess_M2ePro_Model_Resource_Component_Parent_Abstract $resourceModel
     * @return Ess_M2ePro_Model_Resource_Marketplace_Collection
     */
    private function createWithChildMode($resourceModel)
    {
        /** @var Ess_M2ePro_Model_Resource_Marketplace_Collection */
        return Mage::getResourceModel('M2ePro/Marketplace_Collection', $resourceModel);
    }
}