<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m01_FixEbayQtyReservationDays extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $ebayAccountTableName = $this->_installer->getFullTableName('ebay_account');
        $query = $this->_installer->getConnection()
            ->select()
            ->from($ebayAccountTableName, array('magento_orders_settings', 'account_id'))
            ->query();

        while ($row = $query->fetch()) {
            $magentoOrderSettings = Mage::helper('M2ePro')->jsonDecode($row['magento_orders_settings']);

            if (!$this->isQtyReserveDaysZero($magentoOrderSettings)) {
                continue;
            }

            $magentoOrderSettings['qty_reservation']['days'] = 1;

            $this->_installer->getConnection()->update(
                $ebayAccountTableName,
                array('magento_orders_settings' => Mage::helper('M2ePro')->jsonEncode($magentoOrderSettings)),
                array('account_id = ?' => $row['account_id'])
            );
        }
    }

    private function isQtyReserveDaysZero($magentoOrderSettings)
    {
        if (!isset($magentoOrderSettings['qty_reservation']['days'])) {
            return false;
        }

        return (int)$magentoOrderSettings['qty_reservation']['days'] === 0;
    }
}
