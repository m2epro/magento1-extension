<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_System_FixItemTables extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'system/fix_item_tables';

    /** @var int (in seconds) */
    protected $_interval = 600;

    protected function performActions()
    {
        $this->fixAmazonItemTable();
        $this->fixWalmartItemTable();

        return true;
    }

    private function fixAmazonItemTable()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Collection $listingProductCollection */
        $listingProductCollection = Mage::getModel(
            'M2ePro/Listing_Product',
            array('child_mode' => Ess_M2ePro_Helper_Component_Amazon::NICK)
        )->getCollection();

        $listingProductCollection
            ->addFieldToFilter('status', array('neq' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED));

        $listingProductCollection->getSelect()->joinLeft(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'main_table.listing_id = l.id',
            array()
        );
        $listingProductCollection->getSelect()->joinLeft(
            array('ai' => Mage::getResourceModel('M2ePro/Amazon_Item')->getMainTable()),
            <<<SQL
second_table.sku = ai.sku
AND l.account_id = ai.account_id
AND l.marketplace_id = ai.marketplace_id
SQL
            ,
            array()
        );
        $listingProductCollection->addFieldToFilter('ai.sku', array('null' => true));
        $listingProductCollection->addFieldToFilter('second_table.sku', array('notnull' => true));

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Linking $linkingObject */
        $linkingObject = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Type_List_Linking');

        $this->getOperationHistory()->addText("Bad amazon products: " . $listingProductCollection->count());
        $this->getOperationHistory()->addTimePoint(__CLASS__ . '::' . __METHOD__, 'Fix Amazon Items');

        /** @var Ess_M2ePro_Helper_Module_Configuration $moduleConfiguration */
        $moduleConfiguration = Mage::helper('M2ePro/Module_Configuration');
        foreach ($listingProductCollection->getItems() as $listingProduct) {
            if ($listingProduct->getMagentoProduct()->isGroupedType()
                && $moduleConfiguration->isGroupedProductModeSet()
            ) {
                $listingProduct->setSetting('additional_data', 'grouped_product_mode', 1);
                $listingProduct->save();
            }

            $linkingObject->setListingProduct($listingProduct);
            $linkingObject->createAmazonItem();
        }
        $this->getOperationHistory()->saveTimePoint(__CLASS__ . '::' . __METHOD__);
    }

    private function fixWalmartItemTable()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Collection $listingProductCollection */
        $listingProductCollection = Mage::getModel(
            'M2ePro/Listing_Product',
            array('child_mode' => Ess_M2ePro_Helper_Component_Walmart::NICK)
        )->getCollection();

        $listingProductCollection
            ->addFieldToFilter('status', array('neq' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED));

        $listingProductCollection->getSelect()->joinLeft(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'main_table.listing_id = l.id',
            array()
        );
        $listingProductCollection->getSelect()->joinLeft(
            array('ai' => Mage::getResourceModel('M2ePro/Walmart_Item')->getMainTable()),
            <<<SQL
second_table.sku = ai.sku
AND l.account_id = ai.account_id
AND l.marketplace_id = ai.marketplace_id
SQL
            ,
            array()
        );
        $listingProductCollection->addFieldToFilter('ai.sku', array('null' => true));
        $listingProductCollection->addFieldToFilter('second_table.sku', array('notnull' => false));

        $this->getOperationHistory()->addText("Bad walmart products: " . $listingProductCollection->count());
        $this->getOperationHistory()->addTimePoint(__CLASS__ . '::' . __METHOD__, 'Fix Walmart Items');

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_List_Linking $linkingObject */
        $linkingObject = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Type_List_Linking');
        /** @var Ess_M2ePro_Helper_Module_Configuration $moduleConfiguration */
        $moduleConfiguration = Mage::helper('M2ePro/Module_Configuration');
        foreach ($listingProductCollection->getItems() as $listingProduct) {
            if ($listingProduct->getMagentoProduct()->isGroupedType()
                && $moduleConfiguration->isGroupedProductModeSet()
            ) {
                $listingProduct->setSetting('additional_data', 'grouped_product_mode', 1);
                $listingProduct->save();
            }

            $linkingObject->setListingProduct($listingProduct);
            $linkingObject->createWalmartItem();
        }
        $this->getOperationHistory()->saveTimePoint(__CLASS__ . '::' . __METHOD__);
    }
}
