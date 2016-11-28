<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Templates_Repricing
    extends Ess_M2ePro_Model_Amazon_Synchronization_Templates_Abstract
{
    //########################################

    protected function getNick()
    {
        return '/repricing/update/';
    }

    protected function getTitle()
    {
        return 'Repricing';
    }

    // ---------------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 20;
    }

    protected function isPossibleToRun()
    {
        if (!parent::isPossibleToRun()) {
            return false;
        }

        return Mage::helper('M2ePro/Component_Amazon_Repricing')->isEnabled();
    }

    //########################################

    protected function performActions()
    {
        $changedListingsProductsRepricing = $this->getChangedListingsProductsRepricing();
        if (empty($changedListingsProductsRepricing)) {
            return;
        }

        $processRequiredListingsProductsIds      = array();
        $resetProcessRequiredListingsProductsIds = array();

        foreach ($changedListingsProductsRepricing as $listingProductRepricing) {
            try {

                if ($this->isProcessRequired($listingProductRepricing)) {
                    $processRequiredListingsProductsIds[] = $listingProductRepricing->getListingProductId();
                    continue;
                }

                if ($listingProductRepricing->isProcessRequired()) {
                    $resetProcessRequiredListingsProductsIds[] = $listingProductRepricing->getListingProductId();
                }

            } catch (Exception $exception) {

                /** @var Ess_M2ePro_Model_Amazon_Listing_Log $logModel */
                $logModel = Mage::getModel('M2ePro/Amazon_Listing_Log');
                $listingProduct = $listingProductRepricing->getListingProduct();

                $logModel->addProductMessage(
                    $listingProduct->getListingId(),
                    $listingProduct->getProductId(),
                    $listingProduct->getId(),
                    Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                    $logModel->getNextActionId(),
                    Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN,
                    $exception->getMessage(),
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
                );

                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }
        }

        if (!empty($processRequiredListingsProductsIds)) {
            Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->markAsProcessRequired(
                array_unique($processRequiredListingsProductsIds)
            );
        }

        if (!empty($resetProcessRequiredListingsProductsIds)) {
            Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->resetProcessRequired(
                array_unique($resetProcessRequiredListingsProductsIds)
            );
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Repricing[]
     */
    private function getChangedListingsProductsRepricing()
    {
        $changedListingsProducts = $this->getChangesHelper()->getInstances(
            array(Ess_M2ePro_Model_ProductChange::UPDATE_ATTRIBUTE_CODE)
        );

        if (empty($changedListingsProducts)) {
            return array();
        }

        $listingProductRepricingCollection = Mage::getResourceModel(
            'M2ePro/Amazon_Listing_Product_Repricing_Collection'
        );
        $listingProductRepricingCollection->addFieldToFilter(
            'listing_product_id', array('in' => array_keys($changedListingsProducts))
        );

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Repricing[] $listingsProductsRepricing */
        $listingsProductsRepricing = $listingProductRepricingCollection->getItems();
        if (empty($listingsProductsRepricing)) {
            return array();
        }

        foreach ($listingsProductsRepricing as $listingProductRepricing) {
            $listingProductRepricing->setListingProduct(
                $changedListingsProducts[$listingProductRepricing->getListingProductId()]
            );
        }

        return $listingsProductsRepricing;
    }

    private function isProcessRequired(Ess_M2ePro_Model_Amazon_Listing_Product_Repricing $listingProductRepricing)
    {
        $isDisabled       = $listingProductRepricing->isDisabled();
        $isOnlineDisabled = $listingProductRepricing->isOnlineDisabled();

        if ($isDisabled && $isOnlineDisabled) {
            return false;
        }

        if ($listingProductRepricing->getRegularPrice() == $listingProductRepricing->getOnlineRegularPrice() &&
            $listingProductRepricing->getMinPrice()     == $listingProductRepricing->getOnlineMinPrice() &&
            $listingProductRepricing->getMaxPrice()     == $listingProductRepricing->getOnlineMaxPrice() &&
            $isDisabled == $isOnlineDisabled
        ) {
            return false;
        }

        return true;
    }

    //########################################
}