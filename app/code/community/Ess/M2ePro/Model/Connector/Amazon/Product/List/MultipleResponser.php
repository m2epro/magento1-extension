<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Response getResponseObject($listingProduct)
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_List_MultipleResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Product_Responser
{
    //########################################

    protected function getSuccessfulMessage(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        // M2ePro_TRANSLATIONS
        // Item was successfully Listed
        return 'Item was successfully Listed';
    }

    //########################################

    public function eventAfterProcessing()
    {
        parent::eventAfterProcessing();
        $this->removeSKUsFromQueue();
    }

    protected function inspectProducts()
    {
        parent::inspectProducts();

        $childListingProducts = array();

        foreach ($this->successfulListingProducts as $listingProduct) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            if (!$amazonListingProduct->getVariationManager()->isRelationParentType()) {
                continue;
            }

            $childListingProducts = array_merge(
                $childListingProducts,
                $amazonListingProduct->getVariationManager()->getTypeModel()->getChildListingsProducts()
            );
        }

        if (empty($childListingProducts)) {
            return;
        }

        $runner = Mage::getModel('M2ePro/Synchronization_Templates_Runner');
        $runner->setConnectorModel('Connector_Amazon_Product_Dispatcher');
        $runner->setMaxProductsPerStep(100);

        $inspector = Mage::getModel('M2ePro/Amazon_Synchronization_Templates_Inspector');

        foreach ($childListingProducts as $listingProduct) {

            if (!$inspector->isMeetListRequirements($listingProduct)) {
                continue;
            }

            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');

            $runner->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_LIST, $configurator
            );
        }

        $runner->execute();
    }

    //########################################

    protected function processSuccess(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();

        if ($amazonListingProduct->getVariationManager()->isRelationMode() &&
            !$this->getRequestDataObject($listingProduct)->hasProductId() &&
            empty($params['general_id'])
        ) {
            $this->getLogger()->logListingProductMessage(
                $listingProduct,
                'Unexpected error. The ASIN/ISBN for Parent or Child Product was not returned from Amazon.
                 Operation cannot be finished correctly.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return;
        }

        parent::processSuccess($listingProduct, $params);
    }

    protected function getSuccessfulParams(Ess_M2ePro_Model_Listing_Product $listingProduct, $response)
    {
        if (!is_array($response['asins']) || empty($response['asins'])) {
            return array();
        }

        foreach ($response['asins'] as $key => $asin) {
            if ((int)$key != (int)$listingProduct->getId()) {
                continue;
            }

            return array('general_id' => $asin);
        }

        return array();
    }

    //########################################

    private function removeSKUsFromQueue()
    {
        /** @var Ess_M2ePro_Model_LockItem $lockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick('amazon_list_skus_queue_' . $this->getAccount()->getId());

        if (!$lockItem->isExist()) {
            return;
        }

        $skusToRemove = array();

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($this->listingsProducts as $listingProduct) {
            $skusToRemove[] = (string)$this->getRequestDataObject($listingProduct)->getSku();
        }

        $resultSkus = array_diff($lockItem->getContentData(), $skusToRemove);

        if (empty($resultSkus)) {
            $lockItem->remove();
            return;
        }

        $lockItem->setContentData($resultSkus);
    }

    //########################################
}