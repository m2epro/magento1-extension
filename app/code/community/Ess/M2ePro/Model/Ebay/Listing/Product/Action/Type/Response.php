<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
{
    /**
     * @var array
     */
    private $params = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
     */
    private $configurator = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected $requestData = NULL;

    /**
     * @var array
     */
    protected $requestMetaData = array();

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
        $this->params = $params;
    }

    /**
     * @return array
     */
    protected function getParams()
    {
        return $this->params;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $object
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $object)
    {
        $this->listingProduct = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->listingProduct;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $object
     */
    public function setConfigurator(Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $object)
    {
        $this->configurator = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->configurator;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData $object
     */
    public function setRequestData(Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData $object)
    {
        $this->requestData = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function getRequestData()
    {
        return $this->requestData;
    }

    // ---------------------------------------

    public function getRequestMetaData()
    {
        return $this->requestMetaData;
    }

    public function setRequestMetaData($value)
    {
        $this->requestMetaData = $value;
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

        $requestMetadata     = $this->getRequestMetaData();
        $variationIdsIndexes = !empty($requestMetadata['variation_ids_indexes'])
            ? $requestMetadata['variation_ids_indexes'] : array();

        foreach ($this->getListingProduct()->getVariations(true) as $variation) {

            if ($this->getRequestData()->hasVariations()) {

                if (!isset($variationIdsIndexes[$variation->getId()])) {
                    continue;
                }

                $requestVariation = $requestVariations[$variationIdsIndexes[$variation->getId()]];

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
        if (isset($this->params['status_changer'])) {
            $data['status_changer'] = (int)$this->params['status_changer'];
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
            $data['online_bids'] = NULL;
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

        if ($metadata["is_listing_type_fixed"]) {

            $data['online_start_price'] = NULL;
            $data['online_reserve_price'] = NULL;
            $data['online_buyitnow_price'] = NULL;

            if ($this->getRequestData()->hasVariations()) {

                // out_of_stock_control_result key is not presented in request data,
                // if request was performed before code upgrade
                if (!$this->getRequestData()->hasOutOfStockControlResult()) {
                    $calculateWithEmptyQty = $this->getEbayListingProduct()->getOutOfStockControl();
                } else {
                    $calculateWithEmptyQty = $this->getRequestData()->getOutOfStockControlResult();
                }

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

        if ($this->getRequestData()->hasDescription()) {
            $data['online_description'] = $this->getRequestData()->getDescription();
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

    protected function appendOutOfStockValues($data)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if ($this->getRequestData()->hasOutOfStockControl()) {
            $data['additional_data']['out_of_stock_control'] = $this->getRequestData()->getOutOfStockControl();
        }

        return $data;
    }

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

    protected function appendImagesValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['images_data'])) {
            return $data;
        }

        $data['online_images'] = json_encode($requestMetadata['images_data']);

        return $data;
    }

    protected function appendCategoriesValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['categories_data'])) {
            return $data;
        }

        $data['online_categories_data'] = json_encode($requestMetadata['categories_data']);

        return $data;
    }

    protected function appendPaymentValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['payment_data'])) {
            return $data;
        }

        $data['online_payment_data'] = json_encode($requestMetadata['payment_data']);

        return $data;
    }

    protected function appendShippingValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['shipping_data'])) {
            return $data;
        }

        $data['online_shipping_data'] = json_encode($requestMetadata['shipping_data']);

        return $data;
    }

    protected function appendReturnValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['return_data'])) {
            return $data;
        }

        $data['online_return_data'] = json_encode($requestMetadata['return_data']);

        return $data;
    }

    protected function appendOtherValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['other_data'])) {
            return $data;
        }

        $data['online_other_data'] = json_encode($requestMetadata['other_data']);

        return $data;
    }

    //########################################

    protected function runAccountPickupStoreStateUpdater()
    {
        $pickupStoreStateUpdater = Mage::getModel('M2ePro/Ebay_Listing_Product_PickupStore_State_Updater');
        $pickupStoreStateUpdater->setListingProduct($this->getListingProduct());
        $pickupStoreStateUpdater->process();
    }

    //########################################
}