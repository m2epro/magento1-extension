<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Connector_Product_Stop_Requester
    extends Ess_M2ePro_Model_Walmart_Connector_Product_Requester
{
    //########################################

    public function getCommand()
    {
        return array('product','update','entities');
    }

    //########################################

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_STOP;
    }

    protected function getLockIdentifier()
    {
        $identifier = parent::getLockIdentifier();

        if (!empty($this->_params['remove'])) {
            $identifier .= '_and_remove';
        }

        return $identifier;
    }

    protected function getLogsAction()
    {
        return !empty($this->_params['remove']) ?
               Ess_M2ePro_Model_Listing_Log::ACTION_STOP_AND_REMOVE_PRODUCT :
               Ess_M2ePro_Model_Listing_Log::ACTION_STOP_PRODUCT_ON_COMPONENT;
    }

    //########################################

    protected function validateListingProduct()
    {
        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $this->_listingProduct->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();

        $parentListingProduct = null;

        if ($variationManager->isRelationChildType()) {
            $parentListingProduct = $variationManager->getTypeModel()->getParentListingProduct();
        }

        $validator = $this->getValidatorObject();

        $validationResult = $validator->validate();

        if (!$validationResult && $this->_listingProduct->isDeleted()) {
            if ($parentListingProduct !== null) {
                $parentListingProduct->loadInstance($parentListingProduct->getId());

                /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartParentListingProduct */
                $walmartParentListingProduct = $parentListingProduct->getChildObject();
                $walmartParentListingProduct->getVariationManager()->getTypeModel()->getProcessor()->process();
            }

            return false;
        }

        foreach ($validator->getMessages() as $messageData) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData($messageData['text'], $messageData['type']);

            $this->storeLogMessage($message);
        }

        return $validationResult;
    }

    //########################################

    protected function validateAndProcessParentListingProduct()
    {
        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $this->_listingProduct->getChildObject();

        if (!$walmartListingProduct->getVariationManager()->isRelationParentType()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Listing_Product[] $childListingsProducts */
        $childListingsProducts = $walmartListingProduct->getVariationManager()
            ->getTypeModel()
            ->getChildListingsProducts();

        $filteredByStatusChildListingProducts = $this->filterChildListingProductsByStatus($childListingsProducts);
        $filteredByStatusNotLockedChildListingProducts = $this->filterLockedChildListingProducts(
            $filteredByStatusChildListingProducts
        );

        if (empty($this->_params['remove']) && empty($filteredByStatusNotLockedChildListingProducts)) {
            $this->_listingProduct->setData('no_child_for_processing', true);
            return false;
        }

        $notLockedChildListingProducts = $this->filterLockedChildListingProducts($childListingsProducts);

        if (count($childListingsProducts) != count($notLockedChildListingProducts)) {
            $this->_listingProduct->setData('child_locked', true);
            return false;
        }

        if (!empty($this->_params['remove'])) {
            $walmartListingProduct->getVariationManager()->switchModeToAnother();

            $this->_listingProduct->addData(
                array(
                'status' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
                )
            );
            $this->_listingProduct->save();

            $this->getProcessingRunner()->stop();

            foreach ($childListingsProducts as $childListingProduct) {
                if ($childListingProduct->isNotListed() ||
                    $childListingProduct->isInactive() ||
                    $childListingProduct->isBlocked()
                ) {
                    $childListingProduct->deleteInstance();
                }
            }

            $this->_listingProduct->deleteInstance();
        }

        if (empty($filteredByStatusNotLockedChildListingProducts)) {
            return true;
        }

        $childListingsProductsIds = array();
        foreach ($filteredByStatusNotLockedChildListingProducts as $listingProduct) {
            $childListingsProductsIds[] = $listingProduct->getId();
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('id', array('in' => $childListingsProductsIds));

        /** @var Ess_M2ePro_Model_Listing_Product[] $processChildListingsProducts */
        $processChildListingsProducts = $listingProductCollection->getItems();
        if (empty($processChildListingsProducts)) {
            return true;
        }

        foreach ($processChildListingsProducts as $childListingProduct) {
            $processingRunner = Mage::getModel('M2ePro/Walmart_Connector_Product_ProcessingRunner');
            $processingRunner->setParams(
                array(
                'listing_product_id' => $childListingProduct->getId(),
                'configurator'       => $this->_listingProduct->getActionConfigurator()->getData(),
                'action_type'        => $this->getActionType(),
                'lock_identifier'    => $this->getLockIdentifier(),
                'requester_params'   => array_merge($this->_params, array('is_parent_action' => true)),
                'group_hash'         => $this->_listingProduct->getProcessingAction()->getGroupHash(),
                )
            );

            $processingRunner->start();
        }

        return true;
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
            if ((!$childListingProduct->isListed() || !$childListingProduct->isStoppable()) &&
                empty($this->_params['remove'])
            ) {
                continue;
            }

            $resultListingProducts[] = $childListingProduct;
        }

        return $resultListingProducts;
    }

    //########################################
}
