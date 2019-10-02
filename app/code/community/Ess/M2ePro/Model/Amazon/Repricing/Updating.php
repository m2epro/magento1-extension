<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Repricing_Updating extends Ess_M2ePro_Model_Amazon_Repricing_Abstract
{
    //########################################

    /**
     * @param Ess_M2ePro_Model_Amazon_Listing_Product_Repricing[] $listingsProductsRepricing
     * @return bool|array
     */
    public function process(array $listingsProductsRepricing)
    {
        $changesData                      = array();
        $updatedListingProductsRepricing  = array();
        $updatedSkus                      = array();

        foreach ($listingsProductsRepricing as $listingProductRepricing) {
            $changeData = $this->getChangeData($listingProductRepricing);
            if ($changeData && !in_array($changeData['sku'], $updatedSkus, true)) {
                $changesData[] = $changeData;
                $updatedSkus[] = $changeData['sku'];
                $updatedListingProductsRepricing[] = $listingProductRepricing;
            }
        }

        if (!$this->sendData($changesData)) {
            return false;
        }

        $this->updateListingsProductsRepricing($updatedListingProductsRepricing);

        return $updatedSkus;
    }

    //########################################

    protected function getChangeData(Ess_M2ePro_Model_Amazon_Listing_Product_Repricing $listingProductRepricing)
    {
        $isDisabled = $listingProductRepricing->isDisabled();

        if ($isDisabled && !$listingProductRepricing->isOnlineManaged()) {
            return false;
        }

        $regularPrice = $listingProductRepricing->getRegularPrice();
        $minPrice     = $listingProductRepricing->getMinPrice();
        $maxPrice     = $listingProductRepricing->getMaxPrice();

        if ($isDisabled   == $listingProductRepricing->getLastUpdatedIsDisabled() &&
            $regularPrice == $listingProductRepricing->getLastUpdatedRegularPrice() &&
            $minPrice     == $listingProductRepricing->getLastUpdatedMinPrice() &&
            $maxPrice     == $listingProductRepricing->getLastUpdatedMaxPrice()
        ) {
            return false;
        }

        return array(
            'sku' => $listingProductRepricing->getAmazonListingProduct()->getSku(),
            'regular_product_price'   => $regularPrice,
            'minimal_product_price'   => $minPrice,
            'maximal_product_price'   => $maxPrice,
            'is_calculation_disabled' => $isDisabled,
        );
    }

    protected function sendData(array $changesData)
    {
        try {
            $result = $this->getHelper()->sendRequest(
                Ess_M2ePro_Helper_Component_Amazon_Repricing::COMMAND_SYNCHRONIZE_USER_CHANGES,
                array(
                    'account_token' => $this->getAmazonAccountRepricing()->getToken(),
                    'offers'        => Mage::helper('M2ePro')->jsonEncode($changesData),
                )
            );
        } catch (Exception $exception) {
            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($exception->getMessage()),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );

            Mage::helper('M2ePro/Module_Exception')->process($exception, false);
            return false;
        }

        $this->processErrorMessages($result['response']);
        return true;
    }

    protected function updateListingsProductsRepricing(array $updatedProducts)
    {
        $resource  = Mage::getSingleton('core/resource');
        $connWrite = $resource->getConnection('core_write');

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Repricing $updatedProduct */
        foreach ($updatedProducts as $updatedProduct) {
            $connWrite->update(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_amazon_listing_product_repricing'),
                array(
                    'last_updated_regular_price'      => $updatedProduct->getRegularPrice(),
                    'last_updated_min_price'          => $updatedProduct->getMinPrice(),
                    'last_updated_max_price'          => $updatedProduct->getMaxPrice(),
                    'last_updated_is_disabled'        => $updatedProduct->isDisabled(),
                    'update_date'                     => Mage::helper('M2ePro')->getCurrentGmtDate(),
                ),
                array('listing_product_id = ?' => $updatedProduct->getListingProductId())
            );
        }
    }

    //########################################
}
