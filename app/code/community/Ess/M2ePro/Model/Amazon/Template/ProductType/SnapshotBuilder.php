<?php


class Ess_M2ePro_Model_Amazon_Template_ProductType_SnapshotBuilder
    extends Ess_M2ePro_Model_ActiveRecord_SnapshotBuilder
{
    /**
     * @return array
     */
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