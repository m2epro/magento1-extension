<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y21_m12_AmazonOrdersFbaStore extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $query = $this->_installer->getConnection()
            ->select()
            ->from($this->_installer->getFullTableName('amazon_account'))
            ->query();

        while ($row = $query->fetch()) {
            $data = Mage::helper('M2ePro')->jsonDecode($row['magento_orders_settings']);

            $data['fba']['store_mode'] = '0';
            $data['fba']['store_id'] = null;

            $this->_installer->getConnection()->update(
                $this->_installer->getFullTableName('amazon_account'),
                array('magento_orders_settings' => Mage::helper('M2ePro')->jsonEncode($data)),
                array('account_id = ?' => $row['account_id'])
            );
        }
    }

    //########################################
}
