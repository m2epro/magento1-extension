<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Synchronization_Task_Defaults_DeletedProducts
    extends Ess_M2ePro_Model_Synchronization_Task_Defaults_Abstract
{
    //####################################

    protected function getNick()
    {
        return '/deleted_products/';
    }

    protected function getTitle()
    {
        return 'Remove Deleted Products';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 30;
    }

    // -----------------------------------

    protected function intervalIsEnabled()
    {
        return true;
    }

    //####################################

    protected function performActions()
    {
        $this->deleteListingsProducts();
        $this->unmapListingsOther();
        $this->deleteItems();
    }

    //####################################

    private function deleteListingsProducts()
    {
        $collection = Mage::getModel('M2ePro/Listing_Product')->getCollection();

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('product_id');
        $collection->getSelect()->distinct(true);

        $entityTableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');

        $collection->getSelect()->joinLeft(
            array('cpe'=>$entityTableName), '(cpe.entity_id = `main_table`.product_id)',array('entity_id')
        );

        $collection->getSelect()->where('cpe.entity_id IS NULL');

        $tempProductsIds = array();
        $rows = $collection->toArray();

        foreach ($rows['items'] as $row) {

            if (in_array((int)$row['product_id'],$tempProductsIds)) {
                continue;
            }

            $tempProductsIds[] = (int)$row['product_id'];

            Mage::getModel('M2ePro/Listing')->removeDeletedProduct((int)$row['product_id']);
            Mage::getModel('M2ePro/ProductChange')->removeDeletedProduct((int)$row['product_id']);
        }
    }

    private function unmapListingsOther()
    {
        $collection = Mage::getModel('M2ePro/Listing_Other')->getCollection();

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('product_id');
        $collection->getSelect()->distinct(true);
        $collection->getSelect()->where('product_id IS NOT NULL');

        $entityTableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');

        $collection->getSelect()->joinLeft(
            array('cpe'=>$entityTableName), '(cpe.entity_id = `main_table`.product_id)',array('entity_id')
        );

        $collection->getSelect()->where('cpe.entity_id IS NULL');

        $tempProductsIds = array();
        $rows = $collection->toArray();

        foreach ($rows['items'] as $row) {

            if (in_array((int)$row['product_id'],$tempProductsIds)) {
                continue;
            }

            $tempProductsIds[] = (int)$row['product_id'];

            Mage::getModel('M2ePro/Listing_Other')->unmapDeletedProduct((int)$row['product_id']);
            Mage::getModel('M2ePro/ProductChange')->removeDeletedProduct((int)$row['product_id']);
        }
    }

    private function deleteItems()
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

            $entityTableName = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity');

            $collection->getSelect()->joinLeft(
                array('cpe'=>$entityTableName), '(cpe.entity_id = `main_table`.product_id)', array('entity_id')
            );

            $collection->getSelect()->where('cpe.entity_id IS NULL');

            $tempProductsIds = array();
            $rows = $collection->toArray();

            foreach ($rows['items'] as $row) {

                if (in_array((int)$row['product_id'],$tempProductsIds)) {
                    continue;
                }

                $tempProductsIds[] = (int)$row['product_id'];

                Mage::getSingleton('M2ePro/Item')->removeDeletedProduct((int)$row['product_id'], $component);
                Mage::getModel('M2ePro/ProductChange')->removeDeletedProduct((int)$row['product_id']);
            }
        }
    }

    //####################################
}