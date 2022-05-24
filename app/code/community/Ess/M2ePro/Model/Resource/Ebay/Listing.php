<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Ebay_Listing
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Listing', 'listing_id');
    }

    //########################################

    public function getProductCollection($listingId)
    {
        $collection = Mage::getResourceModel('catalog/product_collection');

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array('id' => 'id'),
            '{{table}}.listing_id='.(int)$listingId
        );

        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array('listing_product_id' => 'listing_product_id')
        );

        return $collection;
    }

    public function updateMotorsAttributesData(
        $listingId,
        array $listingProductIds,
        $attribute,
        $data,
        $overwrite = false
    ) {
        if (empty($listingProductIds)) {
            return;
        }

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        $storeId = (int)$listing->getStoreId();

        $listingProductsCollection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
        $listingProductsCollection->addFieldToFilter('id', array('in' => $listingProductIds));
        $listingProductsCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingProductsCollection->getSelect()->columns(array('product_id'));

        $productIds = $listingProductsCollection->getColumnValues('product_id');

        if ($overwrite) {
            Mage::getSingleton('catalog/product_action')->updateAttributes(
                $productIds,
                array($attribute => $data),
                $storeId
            );
            return;
        }

        /** @var $productCollection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $productCollection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );
        $productCollection->setStoreId($storeId);
        $productCollection->addFieldToFilter('entity_id', array('in' => $productIds));
        $productCollection->addAttributeToSelect($attribute);

        foreach ($productCollection->getItems() as $itemId => $item) {
            $currentAttributeValue = $item->getData($attribute);
            $newAttributeValue = $data;

            if (!empty($currentAttributeValue)) {
                $newAttributeValue = $currentAttributeValue . ',' . $data;
            }

            Mage::getSingleton('catalog/product_action')->updateAttributes(
                array($itemId),
                array($attribute => $newAttributeValue),
                $storeId
            );
        }
    }

    //########################################
}
