<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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

    //########################################

    abstract public function processSuccess(array $response, array $responseParams = array());

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
            $requestData = $this->getRequestData()->getData();

            $variations = array();

            foreach ($this->getRequestData()->getVariations() as $variation) {
                $channelOptions = $variation['specifics'];
                $productOptions = $variation['specifics'];

                if (empty($requestData['variations_specifics_replacements'])) {
                    $variations[] = array(
                        'product_options' => $productOptions,
                        'channel_options' => $channelOptions,
                    );

                    continue;
                }

                foreach ($requestData['variations_specifics_replacements'] as $productValue => $channelValue) {
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

            $data['variations'] = json_encode($variations);
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

        foreach ($this->getListingProduct()->getVariations(true) as $variation) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
            $ebayVariation = $variation->getChildObject();

            if ($this->getRequestData()->hasVariations()) {

                if (!isset($requestVariations[$variation->getId()])) {
                    continue;
                }

                $requestVariation = $requestVariations[$variation->getId()];

                if ($requestVariation['delete']) {
                    $variation->deleteInstance();
                    continue;
                }

                $data = array(
                    'online_sku'   => $requestVariation['sku'],
                    'online_price' => $requestVariation['price'],
                    'add'          => 0,
                    'delete'       => 0,
                );

                $data['online_qty_sold'] = $saveQtySold ? (int)$ebayVariation->getOnlineQtySold() : 0;
                $data['online_qty'] = $requestVariation['qty'] + $data['online_qty_sold'];

                if (!empty($requestVariation['details']['mpn'])) {
                    $variationAdditionalData = $variation->getAdditionalData();
                    $variationAdditionalData['ebay_mpn_value'] = $requestVariation['details']['mpn'];

                    $data['additional_data'] = json_encode($variationAdditionalData);
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
        if ($this->getEbayListingProduct()->isListingTypeFixed()) {
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
        if ($this->getEbayListingProduct()->isListingTypeFixed()) {

            $data['online_start_price'] = NULL;
            $data['online_reserve_price'] = NULL;
            $data['online_buyitnow_price'] = NULL;

            if ($this->getRequestData()->hasVariations()) {

                $calculateWithEmptyQty = false;
                $tempAdditionalData = $this->getListingProduct()->getAdditionalData();

                if ($this->getRequestData()->hasOutOfStockControl()) {
                    $calculateWithEmptyQty = (bool)$this->getRequestData()->getOutOfStockControl();
                } else if (isset($tempAdditionalData['out_of_stock_control'])) {
                    $calculateWithEmptyQty = (bool)$tempAdditionalData['out_of_stock_control'];
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

        if ($this->getRequestData()->hasPrimaryCategory()) {

            $tempPath = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                $this->getRequestData()->getPrimaryCategory(),
                $this->getMarketplace()->getId()
            );

            if ($tempPath) {
                $data['online_category'] = $tempPath.' ('.$this->getRequestData()->getPrimaryCategory().')';
            } else {
                $data['online_category'] = $this->getRequestData()->getPrimaryCategory();
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
            $data['start_date'] = Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString(
                $response['ebay_start_date_raw']
            );
        }

        if (isset($response['ebay_end_date_raw'])) {
            $data['end_date'] = Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString(
                $response['ebay_end_date_raw']
            );
        }

        return $data;
    }

    protected function appendGalleryImagesValues($data, $response, $responseParams)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if (isset($response['is_eps_ebay_images_mode'])) {
            $data['additional_data']['is_eps_ebay_images_mode'] = $response['is_eps_ebay_images_mode'];
        }

        if (!isset($responseParams['is_images_upload_error']) || !$responseParams['is_images_upload_error']) {

            if ($this->getRequestData()->hasImages()) {
                $imagesData = $this->getRequestData()->getImages();

                if (isset($imagesData['images'])) {
                    $data['additional_data']['ebay_product_images_hash'] =
                        Mage::helper('M2ePro/Component_Ebay')->getImagesHash($imagesData['images']);
                }
            }

            if ($this->getRequestData()->hasVariationsImages()) {
                $imagesData = $this->getRequestData()->getVariationsImages();
                $data['additional_data']['ebay_product_variation_images_hash'] =
                    Mage::helper('M2ePro/Component_Ebay')->getImagesHash($imagesData);
            }
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

    //########################################
}