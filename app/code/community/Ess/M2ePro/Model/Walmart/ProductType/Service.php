<?php

class Ess_M2ePro_Model_Walmart_ProductType_Service
{
    /** @var Ess_M2ePro_Model_Walmart_ProductType_Repository */
    private $repository;

    public function __construct()
    {
        $this->repository = Mage::getModel('M2ePro/Walmart_ProductType_Repository');
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_ProductType_Service_ResultOfMassDelete
     */
    public function deleteByIds(array $idsProductTypes)
    {
        $result = new Ess_M2ePro_Model_Walmart_ProductType_Service_ResultOfMassDelete();

        foreach ($idsProductTypes as $productTypeId) {
            $productType = $this->repository->find((int)$productTypeId);
            if ($productType === null) {
                continue;
            }

            if ($this->repository->isUsed($productType)) {
                $result->incrementCountLocked();
                continue;
            }

            $this->repository->delete($productType);
            $result->incrementCountDeleted();
        }

        return $result;
    }
}