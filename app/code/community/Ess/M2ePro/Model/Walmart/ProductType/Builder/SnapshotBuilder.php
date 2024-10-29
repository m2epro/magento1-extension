<?php

class Ess_M2ePro_Model_Walmart_ProductType_Builder_SnapshotBuilder
    extends Ess_M2ePro_Model_ActiveRecord_SnapshotBuilder
{
    public function getSnapshot()
    {
        $productTypeModel = $this->getModel();
        $data = $productTypeModel->getData();
        if (empty($data)) {
            return array();
        }

        return $data;
    }
}
