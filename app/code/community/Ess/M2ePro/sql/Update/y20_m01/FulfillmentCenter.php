<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m01_FulfillmentCenter extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    /**
     * {@inheritDoc}
     */
    public function getBackupTables()
    {
        return array(
            'amazon_order_item'
        );
    }

    /**
     * {@inheritDoc}
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function execute()
    {
        $this->_installer->getTableModifier('amazon_order_item')
            ->addColumn('fulfillment_center_id', 'VARCHAR(10)', 'NULL', 'qty_purchased');

        //----------------------------------------

        $ordersStmt = $this->_installer->getConnection()
            ->select()
            ->from(
                array('o' => $this->_installer->getTablesObject()->getFullName('order')),
                array('id', 'additional_data')
            )
            ->joinLeft(
                array('oi' => $this->_installer->getTablesObject()->getFullName('order_item')),
                'o.id = oi.order_id',
                array('order_items_ids' => "GROUP_CONCAT(oi.id SEPARATOR ',')")
            )
            ->where('o.component_mode = ?', 'amazon')
            ->where('o.additional_data LIKE ?', '%"fulfillment_center_id":%')
            ->group('o.id')
            ->query();

        $dataHelper = Mage::helper('M2ePro');
        while ($row = $ordersStmt->fetch()) {
            $additionalData = $dataHelper->jsonDecode($row['additional_data']);
            if (empty($additionalData['fulfillment_details']['fulfillment_center_id'])) {
                continue;
            }
            $fulfilmentCenterId = $additionalData['fulfillment_details']['fulfillment_center_id'];
            unset($additionalData['fulfillment_details']['fulfillment_center_id']);

            $this->_installer->getConnection()->update(
                $this->_installer->getTablesObject()->getFullName('amazon_order_item'),
                array('fulfillment_center_id' => $fulfilmentCenterId),
                array('order_item_id IN (?)' => explode(',', $row['order_items_ids']))
            );

            $this->_installer->getConnection()->update(
                $this->_installer->getTablesObject()->getFullName('order'),
                array('additional_data' => $dataHelper->jsonEncode($additionalData)),
                array('id = ?' => (int)$row['id'])
            );
        }
    }

    //########################################
}