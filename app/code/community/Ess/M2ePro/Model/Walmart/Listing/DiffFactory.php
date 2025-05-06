<?php

class Ess_M2ePro_Model_Walmart_Listing_DiffFactory
{
    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Diff
     */
    public function create(array $newSnapshotData, array $oldSnapshotData)
    {
        /** @var Ess_M2ePro_Model_Walmart_Listing_Diff $diff */
        $diff = Mage::getModel(
            'M2ePro/Walmart_Listing_Diff'
        );
        $diff->setNewSnapshot($newSnapshotData);
        $diff->setOldSnapshot($oldSnapshotData);

        return $diff;
    }
}