<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Product_Website_Update_Before extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        $productIds = $this->getEventObserver()->getData('product_ids');
        $websiteIds = $this->getEventObserver()->getData('website_ids');
        $action = $this->getAction($this->getEventObserver()->getData('action'));

        if (empty($productIds) || empty($websiteIds) || empty($action)) {
            return;
        }

        $websitesCollection = Mage::getModel('core/store')->getCollection()
            ->addFieldToFilter('website_id', array('in' => $websiteIds));

        $storeIds = $websitesCollection->getColumnValues('store_id');

        $listings = Mage::getModel('M2ePro/Listing')->getCollection();
        $listings->getSelect()->where('store_id IN (?)', $storeIds);
        $listings->getSelect()->where(
            'auto_website_adding_mode != ' . Ess_M2ePro_Model_Listing::ADDING_MODE_NONE . ' OR ' .
            'auto_website_deleting_mode != ' . Ess_M2ePro_Model_Listing::DELETING_MODE_NONE
        );

        $listingsIds = $listings->getItems();

        if (empty($listingsIds)) {
            return;
        }

        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $websiteTable =Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('catalog/product_website');

        $select = $connWrite->select()
            ->from($websiteTable)
            ->where('product_id IN (?)', $productIds);

        $currentProductWebsites = $connWrite->fetchAll($select);

        $websiteUpdates = Mage::getModel('M2ePro/Magento_Product_Websites_Update')->getCollection()
            ->addFieldToFilter('product_id', array('in' => $productIds))->getItems();

        $addWebsiteUpdates = array();
        $deleteWebsiteUpdates = array();

        foreach ($productIds as $productId) {
            foreach ($websiteIds as $websiteId) {

                $websiteUpdate = false;
                foreach ($websiteUpdates as $wUpdate) {
                    /** @var Ess_M2ePro_Model_Magento_Product_Websites_Update $wUpdate */
                    if ($wUpdate->getProductId() == $productId && $wUpdate->getWebsiteId() == $websiteId) {
                        $websiteUpdate = $wUpdate;
                        break;
                    }
                }

                $currentProductWebsite = false;
                foreach ($currentProductWebsites as $productWebsite) {
                    if ($productWebsite['product_id'] == $productId && $productWebsite['website_id'] == $websiteId) {
                        $currentProductWebsite = $productWebsite;
                    }
                }

                if ($action == Ess_M2ePro_Model_Magento_Product_Websites_Update::ACTION_ADD) {
                    $websiteCheckByAction = $currentProductWebsite === false;
                } else {
                    $websiteCheckByAction = $currentProductWebsite !== false;
                }

                if (!$websiteUpdate && $websiteCheckByAction) {
                    $addWebsiteUpdates[] = array(
                        'product_id' => $productId,
                        'action' => $action,
                        'website_id' => $websiteId,
                        'create_date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                    );
                    continue;
                }

                if ($websiteUpdate &&
                    $websiteUpdate->getAction() != $action &&
                    $websiteCheckByAction)
                {
                    $deleteWebsiteUpdates[] = $websiteUpdate->getId();
                    continue;
                }
            }
        }

        $table = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_magento_product_websites_update');

        if (!empty($deleteWebsiteUpdates)) {
            $connWrite->delete($table, '`id` IN (' . implode(',', $deleteWebsiteUpdates) . ')');
        }

        if (!empty($addWebsiteUpdates)) {
            $connWrite->insertMultiple($table, $addWebsiteUpdates);
        }
    }

    //########################################

    private function getAction($action)
    {
        switch ($action) {
            case 'add':
                return Ess_M2ePro_Model_Magento_Product_Websites_Update::ACTION_ADD;

            case 'remove':
                return Ess_M2ePro_Model_Magento_Product_Websites_Update::ACTION_REMOVE;
        }

        return NULL;
    }

    //########################################
}