<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Listing_Product_Save_After extends Ess_M2ePro_Observer_Abstract
{
    //########################################

    public function process()
    {
        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = $this->getEvent()->getData('object');

        $this->processIndexer($listingProduct);

        if ($listingProduct->isComponentModeEbay()) {
            $this->processEbayItemUUID($listingProduct);
        }
    }

    //########################################

    protected function processIndexer(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $resource = Mage::getResourceModel(
            'M2ePro/'.ucfirst($listingProduct->getComponentMode()).'_Listing_Product_Indexer_VariationParent'
        );

        $isChanged = false;
        foreach ($resource->getTrackedFields() as $fieldName) {
            if ($listingProduct->getData($fieldName) != $listingProduct->getOrigData($fieldName)) {
                $isChanged = true;
                break;
            }
        }

        if ($listingProduct->isObjectCreatingState()) {
            $isChanged = true;
        }

        if (!$isChanged) {
            return;
        }

        /** @var Ess_M2ePro_Model_Listing_Product_Indexer_VariationParent_Manager $manager */
        $manager = Mage::getModel(
            'M2ePro/Listing_Product_Indexer_VariationParent_Manager',
            array($listingProduct->getListing())
        );
        $manager->markInvalidated();
    }

    protected function processEbayItemUUID(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->isComponentModeEbay()) {
            return;
        }

        $oldStatus = (int)$listingProduct->getOrigData('status');
        $newStatus = (int)$listingProduct->getData('status');

        $trackedStatuses = array(
            Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
            Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE,
        );

        if (!$listingProduct->isObjectCreatingState() &&
            ($oldStatus == $newStatus || !in_array($newStatus, $trackedStatuses))) {
            return;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        // The Child object is already saved in Resource Model on _afterSave()
        $childObject = Mage::getModel('M2ePro/Ebay_Listing_Product');
        $childObject->addData(
            array(
                'listing_product_id' => $ebayListingProduct->getId(),
                'item_uuid'          => $ebayListingProduct->generateItemUUID()
            )
        );

        $ebayListingProduct->getResource()->save($childObject);
    }

    //########################################
}
