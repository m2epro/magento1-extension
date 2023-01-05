<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract as ChangeProcessor;

abstract class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
{
    const INSTRUCTION_INITIATOR = 'action_response';

    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $_listingProduct = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
     */
    protected $_configurator = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected $_requestData = null;

    /**
     * @var array
     */
    protected $_requestMetaData = array();

    //########################################

    abstract public function processSuccess(array $response, array $responseParams = array());

    //########################################

    protected function prepareMetadata()
    {
        // backward compatibility for case when we have old request data and new response logic
        $metadata = $this->getRequestMetaData();
        if (!isset($metadata["is_listing_type_fixed"])) {
            $metadata["is_listing_type_fixed"] = $this->getEbayListingProduct()->isListingTypeFixed();
            $this->setRequestMetaData($metadata);
        }
    }

    //########################################

    /**
     * @param array $params
     */
    public function setParams(array $params = array())
    {
        $this->_params = $params;
    }

    /**
     * @return array
     */
    protected function getParams()
    {
        return $this->_params;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $object
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $object)
    {
        $this->_listingProduct = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->_listingProduct;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $object
     */
    public function setConfigurator(Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $object)
    {
        $this->_configurator = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->_configurator;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData $object
     */
    public function setRequestData(Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData $object)
    {
        $this->_requestData = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function getRequestData()
    {
        return $this->_requestData;
    }

    // ---------------------------------------

    public function getRequestMetaData()
    {
        return $this->_requestMetaData;
    }

    public function setRequestMetaData($value)
    {
        $this->_requestMetaData = $value;
        return $this;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product
     */
    protected function getEbayListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    protected function getListing()
    {
        return $this->getListingProduct()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing
     */
    protected function getEbayListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getListing()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Marketplace
     */
    protected function getEbayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getListing()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    protected function getEbayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    protected function getMagentoProduct()
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    //########################################

    /**
     * @param $itemId
     * @return Ess_M2ePro_Model_Ebay_Item
     */
    protected function createEbayItem($itemId)
    {
        $data = array(
            'account_id'     => $this->getAccount()->getId(),
            'marketplace_id' => $this->getMarketplace()->getId(),
            'item_id'        => (double)$itemId,
            'product_id'     => (int)$this->getListingProduct()->getProductId(),
            'store_id'       => (int)$this->getListing()->getStoreId()
        );

        if ($this->getRequestData()->isVariationItem() && $this->getRequestData()->getVariations()) {
            $variations = array();
            $requestMetadata = $this->getRequestMetaData();

            foreach ($this->getRequestData()->getVariations() as $variation) {
                $channelOptions = $variation['specifics'];
                $productOptions = $variation['specifics'];

                if (empty($requestMetadata['variations_specifics_replacements'])) {
                    $variations[] = array(
                        'product_options' => $productOptions,
                        'channel_options' => $channelOptions,
                    );

                    continue;
                }

                foreach ($requestMetadata['variations_specifics_replacements'] as $productValue => $channelValue) {
                    if (!isset($productOptions[$channelValue])) {
                        continue;
                    }

                    $productOptions[$productValue] = $productOptions[$channelValue];
                    unset($productOptions[$channelValue]);
                }

                $variations[] = array(
                    'product_options' => $productOptions,
                    'channel_options' => $channelOptions,
                );
            }

            $data['variations'] = Mage::helper('M2ePro')->jsonEncode($variations);
        }

        if ($this->getListingProduct()->getMagentoProduct()->isGroupedType()) {
            $additionalData = $this->getListingProduct()->getAdditionalData();
            $data['additional_data'] = Mage::helper('M2ePro')->jsonEncode(array(
                'grouped_product_mode' => $additionalData['grouped_product_mode']
            ));
        }

        /** @var Ess_M2ePro_Model_Ebay_Item $object */
        $object = Mage::getModel('M2ePro/Ebay_Item');
        $object->setData($data)->save();

        return $object;
    }

    protected function updateVariationsValues($saveQtySold)
    {
        if (!$this->getRequestData()->isVariationItem()) {
            return;
        }

        $requestVariations = $this->getRequestData()->getVariations();

        $requestMetadata = $this->getRequestMetaData();
        $variationMetadata = !empty($requestMetadata['variation_data']) ? $requestMetadata['variation_data'] : array();

        foreach ($this->getListingProduct()->getVariations(true) as $variation) {
            if ($this->getRequestData()->hasVariations()) {
                if (!isset($variationMetadata[$variation->getId()]['index'])) {
                    continue;
                }

                $requestVariation = $requestVariations[$variationMetadata[$variation->getId()]['index']];

                if ($requestVariation['delete']) {
                    $variation->deleteInstance();
                    continue;
                }

                $data = array(
                    'online_sku'   => $requestVariation['sku'],
                    'add'          => 0,
                    'delete'       => 0,
                );

                if (isset($requestVariation['price'])) {
                    $data['online_price'] = $requestVariation['price'];
                }

                /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
                $ebayVariation = $variation->getChildObject();

                $data['online_qty_sold'] = $saveQtySold ? (int)$ebayVariation->getOnlineQtySold() : 0;
                $data['online_qty'] = $requestVariation['qty'] + $data['online_qty_sold'];

                if (!empty($requestVariation['details'])) {
                    $variationAdditionalData = $variation->getAdditionalData();
                    $variationAdditionalData['online_product_details'] = $requestVariation['details'];
                    $data['additional_data'] = Mage::helper('M2ePro')->jsonEncode($variationAdditionalData);
                }

                $variation->addData($data)->save();
            }

            $variation->getChildObject()->setStatus($this->getListingProduct()->getStatus());
        }
    }

    //########################################

    protected function appendStatusHiddenValue($data)
    {
        if (($this->getRequestData()->hasQty() && $this->getRequestData()->getQty() <= 0) ||
            ($this->getRequestData()->hasVariations() && $this->getRequestData()->getVariationQty() <= 0)) {
            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN;
        }

        return $data;
    }

    protected function appendStatusChangerValue($data, $responseParams)
    {
        if (isset($this->_params['status_changer'])) {
            $data['status_changer'] = (int)$this->_params['status_changer'];
        }

        if (isset($responseParams['status_changer'])) {
            $data['status_changer'] = (int)$responseParams['status_changer'];
        }

        return $data;
    }

    // ---------------------------------------

    protected function appendOnlineBidsValue($data)
    {
        $metadata = $this->getRequestMetaData();

        if ($metadata["is_listing_type_fixed"]) {
            $data['online_bids'] = null;
        } else {
            $data['online_bids'] = 0;
        }

        return $data;
    }

    protected function appendOnlineQtyValues($data)
    {
        $data['online_qty_sold'] = 0;

        if ($this->getRequestData()->hasVariations()) {
            $data['online_qty'] = $this->getRequestData()->getVariationQty();
        } else if ($this->getRequestData()->hasQty()) {
            $data['online_qty'] = $this->getRequestData()->getQty();
        }

        return $data;
    }

    protected function appendOnlinePriceValues($data)
    {
        $metadata = $this->getRequestMetaData();

        if ($metadata['is_listing_type_fixed']) {
            $data['online_start_price'] = null;
            $data['online_reserve_price'] = null;
            $data['online_buyitnow_price'] = null;

            if ($this->getRequestData()->hasVariations() && $this->getConfigurator()->isPriceAllowed()) {
                $calculateWithEmptyQty = $this->getEbayListingProduct()->isOutOfStockControlEnabled();
                $data['online_current_price'] = $this->getRequestData()->getVariationPrice($calculateWithEmptyQty);
            } else if ($this->getRequestData()->hasPriceFixed()) {
                $data['online_current_price'] = $this->getRequestData()->getPriceFixed();
            }
        } else {
            if ($this->getRequestData()->hasPriceStart()) {
                $data['online_start_price'] = $this->getRequestData()->getPriceStart();
                $data['online_current_price'] = $this->getRequestData()->getPriceStart();
            }

            if ($this->getRequestData()->hasPriceReserve()) {
                $data['online_reserve_price'] = $this->getRequestData()->getPriceReserve();
            }

            if ($this->getRequestData()->hasPriceBuyItNow()) {
                $data['online_buyitnow_price'] = $this->getRequestData()->getPriceBuyItNow();
            }
        }

        return $data;
    }

    protected function appendOnlineInfoDataValues($data)
    {
        if ($this->getRequestData()->hasSku()) {
            $data['online_sku'] = $this->getRequestData()->getSku();
        }

        if ($this->getRequestData()->hasTitle()) {
            $data['online_title'] = $this->getRequestData()->getTitle();
        }

        if ($this->getRequestData()->hasSubtitle()) {
            $data['online_sub_title'] = $this->getRequestData()->getSubtitle();
        }

        if ($this->getRequestData()->hasDuration()) {
            $data['online_duration'] = $this->getRequestData()->getDuration();
        }

        if ($this->getRequestData()->hasPrimaryCategory()) {
            $tempPath = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                $this->getRequestData()->getPrimaryCategory(),
                $this->getMarketplace()->getId()
            );

            if ($tempPath) {
                $data['online_main_category'] = $tempPath.' ('.$this->getRequestData()->getPrimaryCategory().')';
            } else {
                $data['online_main_category'] = $this->getRequestData()->getPrimaryCategory();
            }
        }

        return $data;
    }

    // ---------------------------------------

    protected function appendItemFeesValues($data, $response)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if (isset($response['ebay_item_fees'])) {
            $data['additional_data']['ebay_item_fees'] = $response['ebay_item_fees'];
        }

        return $data;
    }

    protected function appendStartDateEndDateValues($data, $response)
    {
        if (isset($response['ebay_start_date_raw'])) {
            $data['start_date'] = Mage::helper('M2ePro/Component_Ebay')->timeToString(
                $response['ebay_start_date_raw']
            );
        }

        if (isset($response['ebay_end_date_raw'])) {
            $data['end_date'] = Mage::helper('M2ePro/Component_Ebay')->timeToString(
                $response['ebay_end_date_raw']
            );
        }

        return $data;
    }

    protected function appendGalleryImagesValues($data, $response)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if (isset($response['is_eps_ebay_images_mode'])) {
            $data['additional_data']['is_eps_ebay_images_mode'] = $response['is_eps_ebay_images_mode'];
        }

        return $data;
    }

    protected function appendIsVariationMpnFilledValue($data)
    {
        if (!$this->getRequestData()->hasVariations()) {
            return $data;
        }

        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        $isVariationMpnFilled = false;

        foreach ($this->getRequestData()->getVariations() as $variation) {
            if (empty($variation['details']['mpn'])) {
                continue;
            }

            $isVariationMpnFilled = true;
            break;
        }

        $data['additional_data']['is_variation_mpn_filled'] = $isVariationMpnFilled;

        if (!$isVariationMpnFilled) {
            $data['additional_data']['without_mpn_variation_issue'] = true;
        }

        return $data;
    }

    protected function appendVariationsThatCanNotBeDeleted(array $data, array $response)
    {
        if (!$this->getRequestData()->isVariationItem()) {
            return $data;
        }

        $variations = isset($response['variations_that_can_not_be_deleted'])
            ? $response['variations_that_can_not_be_deleted'] : array();

        $data['additional_data']['variations_that_can_not_be_deleted'] = $variations;

        return $data;
    }

    protected function appendIsVariationValue(array $data)
    {
        $data["online_is_variation"] = $this->getRequestData()->isVariationItem();

        return $data;
    }

    protected function appendIsAuctionType(array $data)
    {
        $metadata = $this->getRequestMetaData();
        $data["online_is_auction_type"] = !$metadata["is_listing_type_fixed"];

        return $data;
    }

    protected function appendDescriptionValues($data)
    {
        if (!$this->getRequestData()->hasDescription()) {
            return $data;
        }

        $data['online_description'] = Mage::helper('M2ePro')->hashString(
            $this->getRequestData()->getDescription(),
            'md5'
        );

        return $data;
    }

    protected function appendImagesValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['images_data'])) {
            return $data;
        }

        $data['online_images'] = Mage::helper('M2ePro')->hashString(
            Mage::helper('M2ePro')->jsonEncode($requestMetadata['images_data']),
            'md5'
        );

        return $data;
    }

    protected function appendCategoriesValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['categories_data'])) {
            return $data;
        }

        $data['online_categories_data'] = Mage::helper('M2ePro')->jsonEncode($requestMetadata['categories_data']);

        return $data;
    }

    protected function appendPartsValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!array_key_exists('parts_data_hash', $requestMetadata)) {
            return $data;
        }

        $data['online_parts_data'] = $requestMetadata['parts_data_hash'];

        return $data;
    }

    protected function appendPaymentValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['payment_data'])) {
            return $data;
        }

        $data['online_payment_data'] = Mage::helper('M2ePro')->hashString(
            Mage::helper('M2ePro')->jsonEncode($requestMetadata['payment_data']),
            'md5'
        );

        return $data;
    }

    protected function appendShippingValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['shipping_data'])) {
            return $data;
        }

        $data['online_shipping_data'] = Mage::helper('M2ePro')->hashString(
            Mage::helper('M2ePro')->jsonEncode($requestMetadata['shipping_data']),
            'md5'
        );

        return $data;
    }

    protected function appendReturnValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['return_data'])) {
            return $data;
        }

        $data['online_return_data'] = Mage::helper('M2ePro')->hashString(
            Mage::helper('M2ePro')->jsonEncode($requestMetadata['return_data']),
            'md5'
        );

        return $data;
    }

    protected function appendOtherValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['other_data'])) {
            return $data;
        }

        $data['online_other_data'] = Mage::helper('M2ePro')->hashString(
            Mage::helper('M2ePro')->jsonEncode($requestMetadata['other_data']),
            'md5'
        );

        return $data;
    }

    public function throwRepeatActionInstructions()
    {
        $instructions = array();

        if ($this->getConfigurator()->isQtyAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 80
            );
        }

        if ($this->getConfigurator()->isPriceAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_PRICE_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 80
            );
        }

        if ($this->getConfigurator()->isTitleAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_TITLE_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 60
            );
        }

        if ($this->getConfigurator()->isSubtitleAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_SUBTITLE_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 60
            );
        }

        if ($this->getConfigurator()->isDescriptionAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 30
            );
        }

        if ($this->getConfigurator()->isImagesAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 30
            );
        }

        if ($this->getConfigurator()->isCategoriesAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 60
            );
        }

        if ($this->getConfigurator()->isShippingAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 60
            );
        }

        if ($this->getConfigurator()->isPaymentAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_PAYMENT_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 60
            );
        }

        if ($this->getConfigurator()->isReturnAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_RETURN_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 60
            );
        }

        if ($this->getConfigurator()->isOtherAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_OTHER_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 30
            );
        }

        if ($this->getConfigurator()->isVariationsAllowed()) {
            $instructions[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => ChangeProcessor::INSTRUCTION_TYPE_VARIATION_IMAGES_DATA_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 30
            );
        }

        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add($instructions);
    }

    //########################################
}
