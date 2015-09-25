<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Validator_Sku_Search
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Validator
{
    private $requestSkus = array();

    private $queueOfSkus = array();

    // ########################################

    public function setRequestSkus(array $skus)
    {
        $this->requestSkus = $skus;
        return $this;
    }

    public function setQueueOfSkus(array $skus)
    {
        $this->queueOfSkus = $skus;
        return $this;
    }

    // ########################################

    public function validate()
    {
        $sku = $this->getSku();

        $generateSkuMode = $this->getBuyListingProduct()->getBuyListing()->isGenerateSkuModeYes();

        if (!$this->isExistInM2ePro($sku, !$generateSkuMode)) {
            return true;
        }

        if (!$generateSkuMode) {
            return false;
        }

        $unifiedSku = $this->getUnifiedSku($sku);
        if ($this->checkSkuRequirements($unifiedSku)) {
            $this->data['sku'] = $unifiedSku;
            return true;
        }

        $baseSku = $this->getBuyListingProduct()->getListingSource()->getSku();

        $unifiedBaseSku = $this->getUnifiedSku($baseSku);
        if ($this->checkSkuRequirements($unifiedBaseSku)) {
            $this->data['sku'] = $unifiedBaseSku;
            return true;
        }

        $unifiedSku = $this->getUnifiedSku();
        if ($this->checkSkuRequirements($unifiedSku)) {
            $this->data['sku'] = $unifiedSku;
            return true;
        }

        $randomSku = $this->getRandomSku();
        if ($this->checkSkuRequirements($randomSku)) {
            $this->data['sku'] = $randomSku;
            return true;
        }

        // M2ePro_TRANSLATIONS
        // Unexpected error during Reference ID generation.
        $this->addMessage('Unexpected error during Reference ID generation.');

        return false;
    }

    // ########################################

    private function getSku()
    {
        if (empty($this->data['sku'])) {
            throw new Ess_M2ePro_Model_Exception('SKU is not defined.');
        }

        return $this->data['sku'];
    }

    private function getUnifiedSku($prefix = 'SKU')
    {
        return $prefix.'_'.$this->getListingProduct()->getProductId().'_'.$this->getListingProduct()->getId();
    }

    private function getRandomSku()
    {
        $hash = sha1(rand(0,10000).microtime(1));
        return $this->getUnifiedSku().'_'.substr($hash, 0, 10);
    }

    // ########################################

    private function checkSkuRequirements($sku)
    {
        if ($sku > Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Validator_Sku_General::SKU_MAX_LENGTH) {
            return false;
        }

        if ($this->isExistInM2ePro($sku, false)) {
            return false;
        }

        return true;
    }

    // ########################################

    private function isExistInM2ePro($sku, $addMessages = false)
    {
        if ($this->isExistInRequestSkus($sku)) {

            // M2ePro_TRANSLATIONS
            // Product with the same Reference ID is being Listed on Rakuten.com.
            $addMessages && $this->addMessage('Product with the same Reference ID is being Listed on Rakuten.com.');
            return true;
        }

        if ($this->isExistInQueueOfSkus($sku)) {

            // M2ePro_TRANSLATIONS
            // Product with the same Reference ID is in process of adding to Rakuten.com.
            $addMessages && $this->addMessage('Product with the same Reference ID is
                                               in process of adding to Rakuten.com.');
            return true;
        }

        if ($this->isExistInM2eProListings($sku)) {

            // M2ePro_TRANSLATIONS
            // Product with the same Reference ID is found in the other M2E Pro Listing.
            $addMessages && $this->addMessage('Product with the same Reference ID is found
                                               in the other M2E Pro Listing.');
            return true;
        }

        if ($this->isExistInOtherListings($sku)) {

            // M2ePro_TRANSLATIONS
            // Product with the same Reference ID is found in M2E Pro 3rd Party Listing.
            $addMessages && $this->addMessage('Product with the same Reference ID is found
                                               in M2E Pro 3rd Party Listing.');
            return true;
        }

        return false;
    }

    // ----------------------------------------

    private function isExistInRequestSkus($sku)
    {
        return in_array($sku, $this->requestSkus);
    }

    private function isExistInQueueOfSkus($sku)
    {
        return in_array($sku, $this->queueOfSkus);
    }

    private function isExistInM2eProListings($sku)
    {
        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Product');
        $collection->getSelect()->join(
            array('l'=>$listingTable),
            '`main_table`.`listing_id` = `l`.`id`',
            array()
        );

        $collection->addFieldToFilter('sku',$sku);
        $collection->addFieldToFilter('account_id',$this->getListingProduct()->getAccount()->getId());

        return $collection->getSize() > 0;
    }

    private function isExistInOtherListings($sku)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Other_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Buy')->getCollection('Listing_Other');

        $collection->addFieldToFilter('sku',$sku);
        $collection->addFieldToFilter('account_id',$this->getListingProduct()->getAccount()->getId());

        return $collection->getSize() > 0;
    }

    // ########################################
}