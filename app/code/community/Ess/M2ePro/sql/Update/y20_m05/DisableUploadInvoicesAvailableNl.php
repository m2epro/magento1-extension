<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m05_DisableUploadInvoicesAvailableNl extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('amazon_marketplace'),
            array('is_upload_invoices_available' => 0),
            array('marketplace_id = ?' => 39)
        );
    }

    //########################################
}
