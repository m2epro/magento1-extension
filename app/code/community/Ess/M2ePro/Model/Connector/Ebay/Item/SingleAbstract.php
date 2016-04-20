<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Ebay_Item_SingleAbstract
    extends Ess_M2ePro_Model_Connector_Ebay_Item_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $listingProduct = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
     */
    protected $requestObject = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
     */
    protected $responseObject = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected $requestDataObject = NULL;

    //########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!is_null($listingProduct->getActionConfigurator())) {
            $actionConfigurator = $listingProduct->getActionConfigurator();
        } else {
            $actionConfigurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
        }

        $this->listingProduct = $listingProduct->loadInstance($listingProduct->getId());
        $this->listingProduct->setActionConfigurator($actionConfigurator);

        parent::__construct($params,$this->listingProduct->getMarketplace(),$this->listingProduct->getAccount());
    }

    //########################################

    public function process()
    {
        $result = parent::process();

        foreach ($this->messages as $message) {

            $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;

            if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
            }

            $this->getLogger()->logListingProductMessage($this->listingProduct, $message, $priority);
        }

        return $result;
    }

    // ---------------------------------------

    protected function eventAfterProcess()
    {
        $this->unlockListingProduct();
    }

    //########################################

    protected function isNeedSendRequest()
    {
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing_product_'.$this->listingProduct->getId());

        if ($lockItem->isExist()) {

            $message = array(
                // M2ePro_TRANSLATIONS
                // Another Action is being processed. Try again when the Action is completed.
                parent::MESSAGE_TEXT_KEY => 'Another Action is being processed. '
                    .'Try again when the Action is completed.',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage(
                $this->listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

            return false;
        }

        $this->lockListingProduct();

        return $this->filterManualListingProduct();
    }

    protected function getRequestTimeout()
    {
        $requestDataObject = $this->getRequestDataObject($this->listingProduct);
        $requestData = $requestDataObject->getData();

        if ($requestData['is_eps_ebay_images_mode'] === false ||
            (is_null($requestData['is_eps_ebay_images_mode']) &&
                $requestData['upload_images_mode'] ==
                    Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Description::UPLOAD_IMAGES_MODE_SELF)) {
            return parent::getRequestTimeout();
        }

        $imagesTimeout = self::TIMEOUT_INCREMENT_FOR_ONE_IMAGE * $requestDataObject->getTotalImagesCount();
        return parent::getRequestTimeout() + $imagesTimeout;
    }

    // ---------------------------------------

    protected function lockListingProduct()
    {
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing_product_'.$this->listingProduct->getId());

        $lockItem->create();
        $lockItem->makeShutdownFunction();
    }

    protected function unlockListingProduct()
    {
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing_product_'.$this->listingProduct->getId());
        $lockItem->remove();
    }

    // ---------------------------------------

    abstract protected function filterManualListingProduct();

    //########################################

    protected function logRequestMessages()
    {
        foreach ($this->getRequestObject()->getWarningMessages() as $message) {

            $message = array(
                parent::MESSAGE_TEXT_KEY => $message,
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_WARNING
            );

            $this->getLogger()->logListingProductMessage(
                $this->listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
     */
    protected function getRequestObject()
    {
        if (is_null($this->requestObject)) {
            $this->requestObject = $this->makeRequestObject($this->listingProduct);
        }
        return $this->requestObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
     */
    protected function getResponseObject()
    {
        if (is_null($this->responseObject)) {
            $this->responseObject = $this->makeResponseObject($this->listingProduct, $this->getRequestDataObject());
        }
        return $this->responseObject;
    }

    // ---------------------------------------

    /**
     * @param array $data
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function buildRequestDataObject(array $data)
    {
        if (is_null($this->requestDataObject)) {
            $this->requestDataObject = parent::makeRequestDataObject($this->listingProduct, $data);
        }
        return $this->requestDataObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function getRequestDataObject()
    {
        return $this->requestDataObject;
    }

    //########################################

    /**
     * @param array $messages
     * @return bool
     *
     * 21919301: (UPC/EAN/ISBN) is missing a value. Enter a value and try again.
     */
    protected function isNewRequiredSpecificNeeded(array $messages)
    {
        foreach ($messages as $message) {
            if ($message[self::MESSAGE_CODE_KEY] == 21919301) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $messages
     * @return bool
     */
    protected function isVariationErrorAppeared(array $messages)
    {
        $errorCodes = array(
            21916587,
            21916626,
            21916603,
            21916664,
            21916585,
            21916582,
            21916672,
        );

        foreach ($messages as $message) {
            if (in_array($message[self::MESSAGE_CODE_KEY], $errorCodes)) {
                return true;
            }
        }

        return false;
    }

    //########################################

    protected function tryToResolveVariationMpnErrors()
    {
        if (!$this->canPerformGetItemCall()) {
            return;
        }

        $variationMpnValues = $this->getVariationMpnDataFromEbay();
        if ($variationMpnValues === false) {
            return;
        }

        $isVariationMpnFilled = !empty($variationMpnValues);

        $this->listingProduct->setSetting('additional_data', 'is_variation_mpn_filled', $isVariationMpnFilled);
        if (!$isVariationMpnFilled) {
            $this->listingProduct->setSetting('additional_data', 'without_mpn_variation_issue', true);
        }

        $this->listingProduct->save();

        if (!empty($variationMpnValues)) {
            $this->fillVariationMpnValues($variationMpnValues);
        }

        $message = array(
            self::MESSAGE_TEXT_KEY => Mage::helper('M2ePro')->__(
                'It has been detected that this Item failed to be updated on eBay because of the errors.
                M2E Pro will automatically try to apply another solution to Revise this Item.'),
            self::MESSAGE_TYPE_KEY => self::MESSAGE_TYPE_WARNING,
        );

        $this->getLogger()->logListingProductMessage($this->listingProduct, $message);

        $this->unlockListingProduct();

        $dispatcher = Mage::getModel('M2ePro/Connector_Ebay_Item_Dispatcher');
        $resultStatus = $dispatcher->process(
            $this->getActionType(), array($this->listingProduct),
            array('status_changer' => $this->params['status_changer'])
        );

        $this->getLogger()->setStatusForce($resultStatus);
        $this->params['logs_action_id'] = $dispatcher->getLogsActionId();
    }

    protected function canPerformGetItemCall()
    {
        if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            return true;
        }

        $getItemCallsCount   = 0;
        $getItemLastCallDate = NULL;

        $maxAllowedGetItemCallsCount = 2;

        $additionalData = $this->listingProduct->getAdditionalData();
        if (!empty($additionalData['get_item_calls_statistic'])) {
            $getItemCallsCount   = $additionalData['get_item_calls_statistic']['count'];
            $getItemLastCallDate = $additionalData['get_item_calls_statistic']['last_call_date'];
        }

        if ($getItemCallsCount >= $maxAllowedGetItemCallsCount) {
            $minAllowedDate = new DateTime('now', new DateTimeZone('UTC'));
            $minAllowedDate->modify('- 1 day');

            if (strtotime($getItemLastCallDate) > $minAllowedDate->format('U')) {
                return false;
            }

            $getItemCallsCount = 0;
        }

        $getItemCallsCount++;
        $getItemLastCallDate = Mage::helper('M2ePro')->getCurrentGmtDate();

        $additionalData['get_item_calls_statistic']['count']           = $getItemCallsCount;
        $additionalData['get_item_calls_statistic']['last_call_date']  = $getItemLastCallDate;

        $this->listingProduct->setSettings('additional_data', $additionalData);
        $this->listingProduct->save();

        return true;
    }

    protected function getVariationMpnDataFromEbay()
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $this->listingProduct->getChildObject();

        /** @var Ess_M2ePro_Model_Connector_Ebay_Virtual $connector */
        $connector = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')->getVirtualConnector(
            'item', 'get', 'info',
            array(
                'item_id' => $ebayListingProduct->getEbayItemIdReal(),
                'parser_type' => 'standard',
                'full_variations_mode' => true
            ), 'result', $this->marketplace, $this->account
        );

        try {
            $itemData = $connector->process();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }

        if (empty($itemData['variations'])) {
            return false;
        }

        $variationMpnValues = array();

        foreach ($itemData['variations'] as $variation) {
            if (empty($variation['specifics']['MPN'])) {
                continue;
            }

            $mpnValue = $variation['specifics']['MPN'];
            unset($variation['specifics']['MPN']);

            $variationMpnValues[] = array(
                'mpn'       => $mpnValue,
                'sku'       => $variation['sku'],
                'specifics' => $variation['specifics'],
            );
        }

        return $variationMpnValues;
    }

    /**
     * @param $variationMpnValues
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function fillVariationMpnValues($variationMpnValues)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Variation_Collection $variationCollection */
        $variationCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product_Variation');
        $variationCollection->addFieldToFilter('listing_product_id', $this->listingProduct->getId());

        /** @var Ess_M2ePro_Model_Mysql4_Listing_Product_Variation_Option_Collection $variationOptionCollection */
        $variationOptionCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection(
            'Listing_Product_Variation_Option'
        );
        $variationOptionCollection->addFieldToFilter(
            'listing_product_variation_id', $variationCollection->getColumnValues('id')
        );

        /** @var Ess_M2ePro_Model_Listing_Product_Variation[] $variations */
        $variations = $variationCollection->getItems();

        /** @var Ess_M2ePro_Model_Listing_Product_Variation_Option[] $variationOptions */
        $variationOptions = $variationOptionCollection->getItems();

        foreach ($variations as $variation) {
            $specifics = array();

            foreach ($variationOptions as $id => $variationOption) {
                if ($variationOption->getListingProductVariationId() != $variation->getId()) {
                    continue;
                }

                $specifics[$variationOption->getAttribute()] = $variationOption->getOption();
                unset($variationOptions[$id]);
            }

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
            $ebayVariation = $variation->getChildObject();

            foreach ($variationMpnValues as $id => $variationMpnValue) {
                if ($ebayVariation->getOnlineSku() != $variationMpnValue['sku'] &&
                    $specifics != $variationMpnValue['specifics']
                ) {
                    continue;
                }

                $additionalData = $variation->getAdditionalData();

                if (!isset($additionalData['ebay_mpn_value']) ||
                    $additionalData['ebay_mpn_value'] != $variationMpnValue['mpn']
                ) {
                    $additionalData['ebay_mpn_value'] = $variationMpnValue['mpn'];

                    $variation->setSettings('additional_data', $additionalData);
                    $variation->save();
                }

                unset($variationMpnValues[$id]);

                break;
            }
        }
    }

    //########################################
}