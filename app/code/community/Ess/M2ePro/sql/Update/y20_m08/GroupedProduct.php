<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m08_GroupedProduct extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     */
    public function execute()
    {
        $this->_installer->getMainConfigModifier()->insert(
            '/general/configuration/', 'grouped_product_mode', '0' // GROUPED_PRODUCT_MODE_OPTIONS
        );

        // ----------------------------------------

        $this->_installer->getTableModifier('order_item')
            ->addColumn('additional_data', 'TEXT', 'NULL', 'qty_reserved', false);

        // ----------------------------------------

        $this->_installer->getTableModifier('ebay_item')
            ->addColumn('additional_data', 'TEXT', 'NULL', 'variations', false);

        $this->_installer->getTableModifier('amazon_item')
            ->addColumn('additional_data', 'TEXT', 'NULL', 'variation_channel_options', false);

        $this->_installer->getTableModifier('walmart_item')
            ->addColumn('additional_data', 'TEXT', 'NULL', 'variation_channel_options', false);

        // ----------------------------------------

        $this->processChannelItems();
        $this->processOrderItems();
    }

    protected function processChannelItems()
    {
        $productsStmt = $this->_installer->getConnection()->select()
            ->from(
                $this->_installer->getTablesObject()->getFullName('listing_product'),
                array('id', 'additional_data')
            )
            ->joinLeft(
                $this->_installer->getTable('catalog_product_entity'),
                'product_id = entity_id',
                array('entity_id')
            )
            ->where('type_id = ?', 'grouped')
            ->query();

        $productIds = array();
        while ($row = $productsStmt->fetch()) {
            $additionalData = (array)json_decode($row['additional_data'], true);
            $additionalData['grouped_product_mode'] = 0; // GROUPED_PRODUCT_MODE_OPTIONS
            $additionalData = json_encode($additionalData);

            $this->_installer->getConnection()->update(
                $this->_installer->getTablesObject()->getFullName('listing_product'),
                array('additional_data' => $additionalData),
                array('id = ?' => (int)$row['id'])
            );

            $productIds[] = (int)$row['entity_id'];
        }

        // ----------------------------------------

        $additionalData = array();
        $additionalData['grouped_product_mode'] = 0; // GROUPED_PRODUCT_MODE_OPTIONS
        $additionalData = json_encode($additionalData);

        $this->_installer->getConnection()->update(
            $this->_installer->getTablesObject()->getFullName('ebay_item'),
            array('additional_data' => $additionalData),
            array('product_id IN (?)' => $productIds)
        );

        $this->_installer->getConnection()->update(
            $this->_installer->getTablesObject()->getFullName('amazon_item'),
            array('additional_data' => $additionalData),
            array('product_id IN (?)' => $productIds)
        );

        $this->_installer->getConnection()->update(
            $this->_installer->getTablesObject()->getFullName('walmart_item'),
            array('additional_data' => $additionalData),
            array('product_id IN (?)' => $productIds)
        );
    }

    protected function processOrderItems()
    {
        $data = array();
        $orderIds = array();
        $magentoOrderIds = array();

        $walmartOrderStmt = $this->_installer->getConnection()
            ->select()
            ->from(
                array('wo' => $this->_installer->getFullTableName('walmart_order')),
                array('order_id')
            )
            ->joinLeft(
                array('o' => $this->_installer->getFullTableName('order')),
                'wo.order_id = o.id',
                array('magento_order_id')
            )
            ->where('wo.status IN (?)', array(0, 1))
            ->where('o.magento_order_id != ?', null)
            ->query();

        while ($row = $walmartOrderStmt->fetch()) {
            $data[] = array(
                'order_id'         => $row['order_id'],
                'magento_order_id' => $row['magento_order_id'],
                'items'            => array()
            );
            $orderIds[] = $row['order_id'];
            $magentoOrderIds[] = $row['magento_order_id'];
        }

        // ----------------------------------------

        $orderItemStmt = $this->_installer->getConnection()->select()
            ->from(
                $this->_installer->getFullTableName('order_item'),
                array('id', 'order_id')
            )
            ->joinLeft(
                $this->_installer->getFullTableName('walmart_order_item'),
                'id = order_item_id',
                array('walmart_order_item_id')
            )
            ->where('order_id IN (?)', $orderIds)
            ->query();

        while ($row = $orderItemStmt->fetch()) {
            foreach ($data as $index => $order) {
                if ($order['order_id'] == $row['order_id']) {
                    $data[$index]['items'][$row['id']] = $row['walmart_order_item_id'];
                }
            }
        }

        // ----------------------------------------

        $magentoOrderItemStmt = $this->_installer->getConnection()->select()
            ->from(
                $this->_installer->getTable('sales_flat_order_item'),
                array('item_id', 'order_id', 'additional_data')
            )
            ->where('order_id IN (?)', $magentoOrderIds)
            ->query();

        while ($row = $magentoOrderItemStmt->fetch()) {

            if (empty($row['additional_data'])) {
                continue;
            }

            $additionalData = (array)Mage::helper('M2ePro')->unserialize($row['additional_data'], true);
            $orderItemId = $additionalData['m2epro_extension']['items'][0]['order_item_id'];

            foreach ($data as $order) {
                if ($order['magento_order_id'] == $row['order_id'] && isset($order['items'][$orderItemId])) {
                    $additionalData['m2epro_extension']['items'][0]['order_item_id'] = $order['items'][$orderItemId];

                    $this->_installer->getConnection()->update(
                        $this->_installer->getTable('sales_flat_order_item'),
                        array('additional_data' => Mage::helper('M2ePro')->serialize($additionalData)),
                        array(
                            'item_id = ?'  => $row['item_id'],
                            'order_id = ?' => $row['order_id']
                        )
                    );

                    break;
                }
            }
        }
    }

    //########################################
}
