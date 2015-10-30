<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Synchronization_Task_Defaults_DeletedProducts
    extends Ess_M2ePro_Model_Synchronization_Task_Defaults_Abstract
{
    //########################################

    /**
     * @return string
     */
    protected function getNick()
    {
        return '/deleted_products/';
    }

    /**
     * @return string
     */
    protected function getTitle()
    {
        return 'Remove Deleted Products';
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getPercentsStart()
    {
        return 30;
    }

    /**
     * @return int
     */
    protected function getPercentsEnd()
    {
        return 40;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    protected function intervalIsEnabled()
    {
        return true;
    }

    //########################################

    protected function performActions()
    {
        $this->deleteListingsProducts();
        $this->unmapListingsOther();
        $this->deleteItems();
    }

    //########################################

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

    //########################################
}