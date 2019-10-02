<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Magento_Product_DetectDirectlyDeleted extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'magento/product/detect_directly_deleted';

    //########################################

    protected function performActions()
    {
        $this->deleteListingsProducts();
        $this->unmapListingsOther();
        $this->deleteItems();
    }

    //########################################

    protected function deleteListingsProducts()
    {
        $collection = Mage::getModel('M2ePro/Listing_Product')->getCollection();

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('product_id');
        $collection->getSelect()->distinct(true);

        $entityTableName = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('catalog_product_entity');

        $collection->getSelect()->joinLeft(
            array('cpe'=>$entityTableName), '(cpe.entity_id = `main_table`.product_id)', array('entity_id')
        );

        $collection->getSelect()->where('cpe.entity_id IS NULL');

        $tempProductsIds = array();
        $rows = $collection->toArray();

        foreach ($rows['items'] as $row) {
            if (in_array((int)$row['product_id'], $tempProductsIds)) {
                continue;
            }

            $tempProductsIds[] = (int)$row['product_id'];

            Mage::getModel('M2ePro/Listing')->removeDeletedProduct((int)$row['product_id']);
        }
    }

    protected function unmapListingsOther()
    {
        $collection = Mage::getModel('M2ePro/Listing_Other')->getCollection();

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('product_id');
        $collection->getSelect()->distinct(true);
        $collection->getSelect()->where('product_id IS NOT NULL');

        $entityTableName = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('catalog_product_entity');

        $collection->getSelect()->joinLeft(
            array('cpe'=>$entityTableName), '(cpe.entity_id = `main_table`.product_id)', array('entity_id')
        );

        $collection->getSelect()->where('cpe.entity_id IS NULL');

        $tempProductsIds = array();
        $rows = $collection->toArray();

        foreach ($rows['items'] as $row) {
            if (in_array((int)$row['product_id'], $tempProductsIds)) {
                continue;
            }

            $tempProductsIds[] = (int)$row['product_id'];

            Mage::getModel('M2ePro/Listing_Other')->unmapDeletedProduct((int)$row['product_id']);
        }
    }

    protected function deleteItems()
    {
        foreach (Mage::helper('M2ePro/Component')->getComponents() as $component) {
            $upperCasedComponent = ucfirst($component);
            $model = Mage::getModel("M2ePro/{$upperCasedComponent}_Item");

            if (!$model) {
                continue;
            }

            $collection = $model->getCollection();

            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $collection->getSelect()->columns('product_id');
            $collection->getSelect()->distinct(true);
            $collection->getSelect()->where('product_id IS NOT NULL');

            $entityTableName = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('catalog_product_entity');

            $collection->getSelect()->joinLeft(
                array('cpe'=>$entityTableName), '(cpe.entity_id = `main_table`.product_id)', array('entity_id')
            );

            $collection->getSelect()->where('cpe.entity_id IS NULL');

            $tempProductsIds = array();
            $rows = $collection->toArray();

            foreach ($rows['items'] as $row) {
                if (in_array((int)$row['product_id'], $tempProductsIds)) {
                    continue;
                }

                $tempProductsIds[] = (int)$row['product_id'];

                Mage::getSingleton('M2ePro/Item')->removeDeletedProduct((int)$row['product_id'], $component);
            }
        }
    }

    //########################################
}
