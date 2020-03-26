<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Product_Delete_Requester
    extends Ess_M2ePro_Model_Amazon_Connector_Product_Requester
{
    //########################################

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

        if (!empty($this->_params['remove'])) {
            $identifier .= '_and_remove';
        }

        return $identifier;
    }

    protected function getLogsAction()
    {
        return !empty($this->_params['remove']) ?
               Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_AND_REMOVE_PRODUCT :
               Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT;
    }

    //########################################

    protected function validateListingProduct()
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->_listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();

        $parentListingProduct = null;

        if ($variationManager->isRelationChildType()) {
            $parentListingProduct = $variationManager->getTypeModel()->getParentListingProduct();
        }

        $validator = $this->getValidatorObject();

        $validationResult = $validator->validate();

        if (!$validationResult && $this->_listingProduct->isDeleted()) {
            if ($parentListingProduct !== null) {
                $parentListingProduct->loadInstance($parentListingProduct->getId());

                /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonParentListingProduct */
                $amazonParentListingProduct = $parentListingProduct->getChildObject();
                $amazonParentListingProduct->getVariationManager()->getTypeModel()->getProcessor()->process();
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
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->_listingProduct->getChildObject();

        if (!$amazonListingProduct->getVariationManager()->isRelationParentType()) {
            return false;
        }

        $childListingsProducts = $amazonListingProduct->getVariationManager()
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
            $this->_listingProduct->addData(
                array(
                'general_id'          => null,
                'is_general_id_owner' => Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO,
                'status'              => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
                )
            );
            $this->_listingProduct->save();

            $amazonListingProduct->getVariationManager()->switchModeToAnother();

            $this->getProcessingRunner()->stop();

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
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('id', array('in' => $childListingsProductsIds));

        foreach ($listingProductCollection->getItems() as $childListingProduct) {
            $processingRunner = Mage::getModel('M2ePro/Amazon_Connector_Product_ProcessingRunner');
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
            if ($childListingProduct->isBlocked() && empty($this->_params['remove'])) {
                continue;
            }

            $resultListingProducts[] = $childListingProduct;
        }

        return $resultListingProducts;
    }

    //########################################
}
