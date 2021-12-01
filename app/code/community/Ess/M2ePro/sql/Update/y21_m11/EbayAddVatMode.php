<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y21_m11_EbayAddVatMode extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_template_selling_format')->addColumn(
            'vat_mode',
            'TINYINT(2) UNSIGNED NOT NULL',
            0,
            'lot_size_attribute'
        );

        $tableName =  $this->_installer->getFullTableName('ebay_template_selling_format');

        $query = $this->_installer->getConnection()
            ->select()
            ->from($tableName)
            ->query();

        while ($row = $query->fetch()) {
            if ($row['vat_percent'] > 0) {
                $this->_installer->getConnection()->update(
                    $tableName,
                    array('vat_mode' => 1),
                    array('template_selling_format_id = ?' => $row['template_selling_format_id'])
                );
            }
        }

        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_sellingformat');
    }

    //########################################
}