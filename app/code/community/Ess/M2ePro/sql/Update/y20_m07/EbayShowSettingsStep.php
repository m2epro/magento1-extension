<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m07_EbayShowSettingsStep extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $stmt = $this->_installer->getConnection()
            ->select()
            ->from($this->_installer->getFullTableName('listing'))
            ->where('component_mode = ?', 'ebay')
            ->query();

        while ($row = $stmt->fetch()) {
            $additionalData = Mage::helper('M2ePro')->jsonDecode($row['additional_data']);
            if (isset($additionalData['show_settings_step'])) {
                unset($additionalData['show_settings_step']);

                $this->_installer->getConnection()
                    ->update(
                        $this->_installer->getFullTableName('listing'),
                        array('additional_data' => Mage::helper('M2ePro')->jsonEncode($additionalData)),
                        array('id = ?' => $row['id'])
                    );
            }
        }
    }

    //########################################
}