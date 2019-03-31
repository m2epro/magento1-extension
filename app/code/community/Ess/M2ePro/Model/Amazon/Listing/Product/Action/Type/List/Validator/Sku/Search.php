<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Search
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Validator
{
    private $skusInProcessing = NULL;

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception
     */
    public function validate()
    {
        $sku = $this->getSku();

        $generateSkuMode = $this->getAmazonListingProduct()->getAmazonListing()->isGenerateSkuModeYes();

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

        if ($this->getVariationManager()->isIndividualType() || $this->getVariationManager()->isRelationChildType()) {
            $baseSku = $this->getAmazonListing()->getSource($this->getMagentoProduct())->getSku();

            $unifiedBaseSku = $this->getUnifiedSku($baseSku);
            if ($this->checkSkuRequirements($unifiedBaseSku)) {
                $this->data['sku'] = $unifiedBaseSku;
                return true;
            }
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
        // SKU generating is not successful.
        $this->addMessage('SKU generating is not successful.');

        return false;
    }

    //########################################

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

    //########################################

    private function checkSkuRequirements($sku)
    {
        if (strlen($sku) > Ess_M2ePro_Helper_Component_Amazon::SKU_MAX_LENGTH) {
            return false;
        }

        if ($this->isExistInM2ePro($sku, false)) {
            return false;
        }

        return true;
    }

    //########################################

    private function isExistInM2ePro($sku, $addMessages = false)
    {
        if ($this->isAlreadyInProcessing($sku)) {
// M2ePro_TRANSLATIONS
// Another Product with the same SKU is being Listed simultaneously with this one. Please change the SKU or enable the Option Generate Merchant SKU.
            $addMessages && $this->addMessage('Another Product with the same SKU is being Listed simultaneously
                                with this one. Please change the SKU or enable the Option Generate Merchant SKU.');
            return true;
        }

        if ($this->isExistInM2eProListings($sku)) {
// M2ePro_TRANSLATIONS
// Product with the same SKU is found in other M2E Pro Listing that is created from the same Merchant ID for the same Marketplace.
            $addMessages && $this->addMessage(
                'Product with the same SKU is found in other M2E Pro Listing that is created
                 from the same Merchant ID for the same Marketplace.'
            );
            return true;
        }

        if ($this->isExistInOtherListings($sku)) {
// M2ePro_TRANSLATIONS
// Product with the same SKU is found in M2E Pro 3rd Party Listing. Please change the SKU or enable the Option Generate Merchant SKU.
            $addMessages && $this->addMessage('Product with the same SKU is found in M2E Pro 3rd Party Listing.
                                            Please change the SKU or enable the Option Generate Merchant SKU.');
            return true;
        }

        return false;
    }

    // ---------------------------------------

    private function isAlreadyInProcessing($sku)
    {
        return in_array($sku, $this->getSkusInProcessing());
    }

    private function isExistInM2eProListings($sku)
    {
        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
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
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');

        $collection->addFieldToFilter('sku',$sku);
        $collection->addFieldToFilter('account_id',$this->getListingProduct()->getAccount()->getId());

        return $collection->getSize() > 0;
    }

    //########################################

    private function getSkusInProcessing()
    {
        if (!is_null($this->skusInProcessing)) {
            return $this->skusInProcessing;
        }

        $processingActionListSkuCollection = Mage::getResourceModel(
            'M2ePro/Amazon_Listing_Product_Action_ProcessingListSku_Collection'
        );
        $processingActionListSkuCollection->addFieldToFilter('account_id', $this->getListing()->getAccountId());

        return $this->skusInProcessing = $processingActionListSkuCollection->getColumnValues('sku');
    }

    //########################################
}