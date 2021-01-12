<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_Search
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Validator
{
    protected $_skusInProcessing = null;

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
            $this->_data['sku'] = $unifiedSku;
            return true;
        }

        if ($this->getVariationManager()->isIndividualType() || $this->getVariationManager()->isRelationChildType()) {
            $baseSku = $this->getAmazonListing()->getSource($this->getMagentoProduct())->getSku();

            $unifiedBaseSku = $this->getUnifiedSku($baseSku);
            if ($this->checkSkuRequirements($unifiedBaseSku)) {
                $this->_data['sku'] = $unifiedBaseSku;
                return true;
            }
        }

        $unifiedSku = $this->getUnifiedSku();
        if ($this->checkSkuRequirements($unifiedSku)) {
            $this->_data['sku'] = $unifiedSku;
            return true;
        }

        $randomSku = $this->getRandomSku();
        if ($this->checkSkuRequirements($randomSku)) {
            $this->_data['sku'] = $randomSku;
            return true;
        }

        $this->addMessage('SKU generating is not successful.');

        return false;
    }

    //########################################

    protected function getSku()
    {
        if (empty($this->_data['sku'])) {
            throw new Ess_M2ePro_Model_Exception('SKU is not defined.');
        }

        return $this->_data['sku'];
    }

    protected function getUnifiedSku($prefix = 'SKU')
    {
        return $prefix.'_'.$this->getListingProduct()->getProductId().'_'.$this->getListingProduct()->getId();
    }

    protected function getRandomSku()
    {
        $hash = sha1(rand(0, 10000).microtime(1));
        return $this->getUnifiedSku().'_'.substr($hash, 0, 10);
    }

    //########################################

    protected function checkSkuRequirements($sku)
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

    protected function isExistInM2ePro($sku, $addMessages = false)
    {
        if ($this->isAlreadyInProcessing($sku)) {
            $addMessages && $this->addMessage(
                'Another Product with the same SKU is being Listed simultaneously
                 with this one. Please change the SKU or enable the Option Generate Merchant SKU.'
            );
            return true;
        }

        if ($this->isExistInM2eProListings($sku)) {
            $addMessages && $this->addMessage(
                'Product with the same SKU is found in other M2E Pro Listing that is created
                 from the same Merchant ID for the same Marketplace.'
            );
            return true;
        }

        if ($this->isExistInOtherListings($sku)) {
            $addMessages && $this->addMessage(
                'Product with the same SKU is found in M2E Pro Unmanaged Listing.
                 Please change the SKU or enable the Option Generate Merchant SKU.'
            );
            return true;
        }

        return false;
    }

    // ---------------------------------------

    protected function isAlreadyInProcessing($sku)
    {
        return in_array($sku, $this->getSkusInProcessing());
    }

    protected function isExistInM2eProListings($sku)
    {
        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->join(
            array('l'=>$listingTable),
            '`main_table`.`listing_id` = `l`.`id`',
            array()
        );

        $collection->addFieldToFilter('sku', $sku);
        $collection->addFieldToFilter('account_id', $this->getListingProduct()->getAccount()->getId());

        return $collection->getSize() > 0;
    }

    protected function isExistInOtherListings($sku)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Other_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Other');

        $collection->addFieldToFilter('sku', $sku);
        $collection->addFieldToFilter('account_id', $this->getListingProduct()->getAccount()->getId());

        return $collection->getSize() > 0;
    }

    //########################################

    protected function getSkusInProcessing()
    {
        if ($this->_skusInProcessing !== null) {
            return $this->_skusInProcessing;
        }

        $processingActionListSkuCollection = Mage::getResourceModel(
            'M2ePro/Amazon_Listing_Product_Action_ProcessingListSku_Collection'
        );
        $processingActionListSkuCollection->addFieldToFilter('account_id', $this->getListing()->getAccountId());

        return $this->_skusInProcessing = $processingActionListSkuCollection->getColumnValues('sku');
    }

    //########################################
}
