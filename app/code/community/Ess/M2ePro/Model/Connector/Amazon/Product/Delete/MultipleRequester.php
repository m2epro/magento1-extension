<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_Delete_MultipleRequester
    extends Ess_M2ePro_Model_Connector_Amazon_Product_Requester
{
    //########################################

    /**
     * @return array
     */
    public function getCommand()
    {
        return array('product','delete','entities');
    }

    //########################################

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_DELETE;
    }

    protected function getLockIdentifier()
    {
        $identifier = parent::getLockIdentifier();

        if (!empty($this->params['remove'])) {
            $identifier .= '_and_remove';
        }

        return $identifier;
    }

    protected function getLogsAction()
    {
        return !empty($this->params['remove']) ?
               Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT :
               Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT;
    }

    //########################################

    protected function validateAndFilterListingsProducts()
    {
        /** @var Ess_M2ePro_Model_Listing_Product[] $parentsForProcessing */
        $parentsForProcessing = array();

        foreach ($this->listingsProducts as $listingProduct) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();
            $variationManager = $amazonListingProduct->getVariationManager();

            $parentListingProduct = null;

            if ($variationManager->isRelationChildType()) {
                $parentListingProduct = $variationManager->getTypeModel()->getParentListingProduct();
            }

            $listingProductId = $listingProduct->getId();

            $validator = $this->getValidatorObject($listingProduct);

            $validationResult = $validator->validate();

            if (!$validationResult && $listingProduct->isDeleted()) {
                $this->removeAndUnlockListingProduct($listingProductId);

                if (!is_null($parentListingProduct)) {
                    $parentListingProductId = $parentListingProduct->getId();
                    $parentsForProcessing[$parentListingProductId] = $parentListingProduct->loadInstance(
                        $parentListingProductId
                    );
                }

                continue;
            }

            foreach ($validator->getMessages() as $message) {

                $this->getLogger()->logListingProductMessage(
                    $listingProduct,
                    $message['text'],
                    $message['type'],
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );
            }

            if ($validationResult) {
                continue;
            }

            $this->removeAndUnlockListingProduct($listingProductId);
        }

        foreach ($parentsForProcessing as $parentListingProduct) {
            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonParentListingProduct */
            $amazonParentListingProduct = $parentListingProduct->getChildObject();
            $amazonParentListingProduct->getVariationManager()->getTypeModel()->getProcessor()->process();
        }
    }

    //########################################

    protected function validateAndProcessParentListingsProducts()
    {
        /** @var Ess_M2ePro_Model_Listing_Product[] $processChildListingsProducts */
        $processChildListingsProducts = array();

        foreach ($this->listingsProducts as $key => $listingProduct) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            if (!$amazonListingProduct->getVariationManager()->isRelationParentType()) {
                continue;
            }

            $childListingsProducts = $amazonListingProduct->getVariationManager()
                ->getTypeModel()
                ->getChildListingsProducts();

            $filteredByStatusChildListingProducts = $this->filterChildListingProductsByStatus($childListingsProducts);
            $filteredByStatusNotLockedChildListingProducts = $this->filterLockedChildListingProducts(
                $filteredByStatusChildListingProducts
            );

            if (empty($this->params['remove'])) {
                if (empty($filteredByStatusNotLockedChildListingProducts)) {
                    $listingProduct->setData('no_child_for_processing', true);
                    continue;
                }

                $processChildListingsProducts = array_merge(
                    $processChildListingsProducts, $filteredByStatusNotLockedChildListingProducts
                );

                unset($this->listingsProducts[$key]);

                continue;
            }

            $notLockedChildListingProducts = $this->filterLockedChildListingProducts($childListingsProducts);

            if (count($childListingsProducts) != count($notLockedChildListingProducts)) {
                $listingProduct->setData('child_locked', true);
                continue;
            }

            $processChildListingsProducts = array_merge(
                $processChildListingsProducts, $filteredByStatusNotLockedChildListingProducts
            );

            $listingProduct->addData(array(
                'general_id'          => null,
                'is_general_id_owner' => Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO,
                'status'              => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
            ));
            $listingProduct->save();

            $amazonListingProduct->getVariationManager()->switchModeToAnother();

            unset($this->listingsProducts[$key]);
            $listingProduct->deleteInstance();
        }

        if (empty($processChildListingsProducts)) {
            return;
        }

        $childListingsProductsIds = array();
        foreach ($processChildListingsProducts as $listingProduct) {
            $childListingsProductsIds[] = $listingProduct->getId();
        }

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('id', array('in' => $childListingsProductsIds));

        $processChildListingsProducts = $listingProductCollection->getItems();
        if (empty($processChildListingsProducts)) {
            return;
        }

        $dispatcherParams = array_merge($this->params, array('is_parent_action' => true));

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Amazon_Product_Dispatcher');
        $processStatus = $dispatcherObject->process(
            $this->getActionType(), $processChildListingsProducts, $dispatcherParams
        );

        if ($processStatus == Ess_M2ePro_Helper_Data::STATUS_ERROR) {
            $this->getLogger()->setStatus(Ess_M2ePro_Helper_Data::STATUS_ERROR);
        }
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product[] $listingProducts
     * @return Ess_M2ePro_Model_Listing_Product[]
     */
    protected function filterChildListingProductsByStatus(array $listingProducts)
    {
        $resultListingProducts = array();

        foreach ($listingProducts as $id => $childListingProduct) {
            if ($childListingProduct->isBlocked() && empty($this->params['remove'])) {
                continue;
            }

            $resultListingProducts[] = $childListingProduct;
        }

        return $resultListingProducts;
    }

    protected function filterLockedListingsProducts()
    {
        parent::filterLockedListingsProducts();

        if (empty($this->params['remove'])) {
            return;
        }

        foreach ($this->listingsProducts as $key => $listingProduct) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            if (!$amazonListingProduct->getVariationManager()->isRelationParentType()) {
                continue;
            }

            if (!$listingProduct->isLockedObject('child_products_in_action')) {
                continue;
            }

            // M2ePro_TRANSLATIONS
            // Another Action is being processed. Try again when the Action is completed.
            $this->getLogger()->logListingProductMessage(
                $listingProduct,
                'Delete and Remove action is not supported if Child Products are in Action.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            unset($this->listingsProducts[$key]);
        }
    }

    //########################################
}