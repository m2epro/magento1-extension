<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m01_EbayOrderUpdates extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $query = $this->_installer->getConnection()
            ->select()
            ->from($this->_installer->getFullTableName('ebay_account'))
            ->query();

        while ($row = $query->fetch()) {
            $data = Mage::helper('M2ePro')->jsonDecode($row['magento_orders_settings']);

            /** Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_CREATE_IMMEDIATELY = 1 */
            /** Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_CREATE_PAID = 3 */
            /** Ess_M2ePro_Model_Ebay_Account::MAGENTO_ORDERS_CREATE_CHECKOUT_AND_PAID = 4 */

            if (isset($data['creation']['mode']) && $data['creation']['mode'] === 1) {
                $reservationDays = !empty($data['creation']['reservation_days'])
                    ? $data['creation']['reservation_days'] : 14;

                $data['creation']['mode'] = 4;
                $data['qty_reservation']['days'] = $reservationDays;
            }

            unset($data['creation']['reservation_days']);

            if (isset($data['creation']['mode']) && $data['creation']['mode'] === 3) {
                $data['creation']['mode'] = 4;
            }

            $this->_installer->getConnection()->update(
                $this->_installer->getFullTableName('ebay_account'),
                array('magento_orders_settings' => Mage::helper('M2ePro')->jsonEncode($data)),
                array('account_id' => $row['account_id'])
            );
        }
    }

    //########################################
}
