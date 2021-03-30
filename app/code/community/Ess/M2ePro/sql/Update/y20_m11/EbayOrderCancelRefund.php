<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m11_EbayOrderCancelRefund extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_order')->addColumn(
            'cancellation_status',
            'TINYINT(2) UNSIGNED NOT NULL',
            '0',
            'payment_status'
        );

        $query = $this->_installer->getConnection()
            ->select()
            ->from($this->_installer->getFullTableName('ebay_account'))
            ->query();

        while ($row = $query->fetch()) {
            $data = Mage::helper('M2ePro')->jsonDecode($row['magento_orders_settings']);

            $data['refund_and_cancellation']['refund_mode'] = '0';

            $this->_installer->getConnection()->update(
                $this->_installer->getFullTableName('ebay_account'),
                array('magento_orders_settings' => Mage::helper('M2ePro')->jsonEncode($data)),
                array('account_id = ?' => $row['account_id'])
            );
        }
    }

    //########################################
}
