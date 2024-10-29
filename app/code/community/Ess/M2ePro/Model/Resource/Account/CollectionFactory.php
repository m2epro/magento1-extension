<?php

class Ess_M2ePro_Model_Resource_Account_CollectionFactory
{
    /**
     * @return Ess_M2ePro_Model_Resource_Account_Collection
     */
    public function createWithWalmartChildMode()
    {
        /** @var Ess_M2ePro_Model_Resource_Account $AccountResource */
        $AccountResource = Mage::getResourceModel('M2ePro/Account');
        $AccountResource->setChildMode(Ess_M2ePro_Helper_Component_Walmart::NICK);

        return $this->createWithChildMode($AccountResource);
    }

    /**
     * @param Ess_M2ePro_Model_Resource_Component_Parent_Abstract $resourceModel
     * @return Ess_M2ePro_Model_Resource_Account_Collection
     */
    private function createWithChildMode($resourceModel)
    {
        /** @var Ess_M2ePro_Model_Resource_Account_Collection */
        return Mage::getResourceModel('M2ePro/Account_Collection', $resourceModel);
    }
}