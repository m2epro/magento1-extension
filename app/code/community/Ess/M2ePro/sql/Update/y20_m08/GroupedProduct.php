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

    //########################################
}
