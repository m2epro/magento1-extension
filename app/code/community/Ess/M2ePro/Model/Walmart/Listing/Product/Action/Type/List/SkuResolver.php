<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_List_SkuResolver
{
    /** @var Ess_M2ePro_Model_Listing_Product */
    protected $_listingProduct = null;

    protected $_skusInProcessing = null;

    protected $_skusInCurrentRequest = array();

    /** @var Ess_M2ePro_Model_Response_Message[] */
    protected $_messages = array();

    //########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->_listingProduct = $listingProduct;
        return $this;
    }

    public function setSkusInCurrentRequest(array $skus)
    {
        $this->_skusInCurrentRequest = $skus;
        return $this;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Response_Message[]
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    //########################################

    public function resolve()
    {
        $sku = $this->getSku();

        if (empty($sku)) {
            $this->addMessage('SKU is not provided. Please, check Listing Settings.');
            return null;
        }

        $generateSkuMode = Mage::helper('M2ePro/Component_Walmart_Configuration')->isGenerateSkuModeYes();

        if (!$this->isExistInM2ePro($sku, !$generateSkuMode)) {
            return $sku;
        }

        if (!$generateSkuMode) {
            return null;
        }

        $unifiedSku = $this->getUnifiedSku($sku);
        if ($this->checkSkuRequirements($unifiedSku)) {
            return $unifiedSku;
        }

        $unifiedSku = $this->getUnifiedSku();
        if ($this->checkSkuRequirements($unifiedSku)) {
            return $unifiedSku;
        }

        return $this->getRandomSku();
    }

    //########################################

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
        if (strlen($sku) > Ess_M2ePro_Helper_Component_Walmart::SKU_MAX_LENGTH) {
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
        if ($this->isAlreadyInCurrentRequest($sku) || $this->isAlreadyInProcessing($sku)) {
            $addMessages && $this->addMessage(
                'Another Product with the same SKU is being Listed simultaneously with this one.
                Please change the SKU or enable the Option Generate Merchant SKU.'
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
                'Product with the same SKU is found in M2E Pro 3rd Party Listing.
                Please change the SKU or enable the Option Generate Merchant SKU.'
            );
            return true;
        }

        return false;
    }

    // ---------------------------------------

    protected function isAlreadyInCurrentRequest($sku)
    {
        return in_array($sku, $this->_skusInCurrentRequest);
    }

    protected function isAlreadyInProcessing($sku)
    {
        return in_array($sku, $this->getSkusInProcessing());
    }

    protected function isExistInM2eProListings($sku)
    {
        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->getSelect()->join(
            array('l' => $listingTable),
            '`main_table`.`listing_id` = `l`.`id`',
            array()
        );

        $collection->addFieldToFilter('sku', $sku);
        $collection->addFieldToFilter('main_table.id', array('neq' => $this->_listingProduct->getId()));
        $collection->addFieldToFilter('l.account_id', $this->getListingProduct()->getAccount()->getId());

        return $collection->getSize() > 0;
    }

    protected function isExistInOtherListings($sku)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Other_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Other');

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

        $processingActionListCollection = Mage::getResourceModel(
            'M2ePro/Walmart_Listing_Product_Action_ProcessingList_Collection'
        );
        $processingActionListCollection->addFieldToFilter(
            'account_id', $this->getListingProduct()->getListing()->getAccountId()
        );

        return $this->_skusInProcessing = $processingActionListCollection->getColumnValues('sku');
    }

    protected function getSku()
    {
        if ($this->getVariationManager()->isPhysicalUnit() &&
            $this->getVariationManager()->getTypeModel()->isVariationProductMatched()
        ) {
            $variations = $this->getListingProduct()->getVariations(true);
            if (empty($variations)) {
                throw new Ess_M2ePro_Model_Exception_Logic(
                    'There are no variations for a variation product.',
                    array(
                        'listing_product_id' => $this->getListingProduct()->getId()
                    )
                );
            }

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);
            $sku = $variation->getChildObject()->getSku();

            if (!empty($sku)) {
                $sku = $this->applySkuModification($sku);
                $sku = $this->removeUnsupportedCharacters($sku);
            }

            /**
             * Only Product Variations created based on Magento Configurable or Grouped Product types can be sold on
             * the Walmart website. So SKU will be taken directly from a Child product and it makes no sense
             * on doing it random.
             */

            //if (strlen($sku) >= Ess_M2ePro_Helper_Component_Walmart::SKU_MAX_LENGTH) {
            //    $sku = Mage::helper('M2ePro')->hashString($sku, 'md5', 'RANDOM_');
            //}

            return $sku;
        }

        $helper = Mage::helper('M2ePro/Component_Walmart_Configuration');

        $sku = '';

        if ($helper->isSkuModeDefault()) {
            $sku = $this->getMagentoProduct()->getSku();
        }

        if ($helper->isSkuModeProductId()) {
            $sku = $this->getMagentoProduct()->getProductId();
        }

        if ($helper->isSkuModeCustomAttribute()) {
            $sku = $this->getMagentoProduct()->getAttributeValue($helper->getSkuCustomAttribute());
        }

        is_string($sku) && $sku = trim($sku);

        if (!empty($sku)) {
            $sku = $this->applySkuModification($sku);
            $sku = $this->removeUnsupportedCharacters($sku);
        }

        return $sku;
    }

    //########################################

    protected function applySkuModification($sku)
    {
        $helper = Mage::helper('M2ePro/Component_Walmart_Configuration');

        if ($helper->isSkuModificationModeNone()) {
            return $sku;
        }

        if ($helper->isSkuModificationModePrefix()) {
            $sku = $helper->getSkuModificationCustomValue() . $sku;
        } elseif ($helper->isSkuModificationModePostfix()) {
            $sku = $sku . $helper->getSkuModificationCustomValue();
        } elseif ($helper->isSkuModificationModeTemplate()) {
            $sku = str_replace('%value%', $sku, $helper->getSkuModificationCustomValue());
        }

        return $sku;
    }

    protected function removeUnsupportedCharacters($sku)
    {
        if (!preg_match('/[.\s-]/', $sku)) {
            return $sku;
        }

        $newSku = preg_replace('/[.\s-]/', '_', $sku);
        $this->addMessage(
            sprintf(
                'The Item SKU will be automatically changed to "%s".
                Special characters, i.e. hyphen (-), space ( ), and period (.), are not allowed by Walmart and
                will be replaced with the underscore ( _ ).
                The Item will remain associated with Magento Product "%s".',
                $newSku, $sku
            ),
            Ess_M2ePro_Model_Response_Message::TYPE_WARNING
        );

        return $newSku;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->_listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getWalmartListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getVariationManager()
    {
        return $this->getWalmartListingProduct()->getVariationManager();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    protected function getMagentoProduct()
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    //########################################

    protected function addMessage($text, $type = Ess_M2ePro_Model_Response_Message::TYPE_ERROR)
    {
        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData($text, $type);

        $this->_messages[] = $message;
    }

    //########################################
}
