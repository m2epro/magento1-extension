<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Connector_Item_Responser
    extends Ess_M2ePro_Model_Ebay_Connector_Command_Pending_Responser
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $_listingProduct = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
     */
    protected $_configurator = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
     */
    protected $_responseObject = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected $_requestDataObject = null;

    /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger $_logger */
    protected $_logger = null;

    protected $_isSuccess = false;

    //########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Connector_Connection_Response $response)
    {
        parent::__construct($params, $response);

        $listingProductId      = $this->_params['product']['id'];
        $this->_listingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);
    }

    //########################################

    protected function validateResponse()
    {
        return true;
    }

    //########################################

    public function failDetected($messageText)
    {
        parent::failDetected($messageText);

        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData(
            $messageText,
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
        );

        $this->getLogger()->logListingProductMessage(
            $this->_listingProduct,
            $message,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );
    }

    //########################################

    protected function processResponseMessages()
    {
        parent::processResponseMessages();

        $messages = array();

        $requestLogMessages = isset($this->_params['product']['request_metadata']['log_messages'])
            ? $this->_params['product']['request_metadata']['log_messages'] : array();

        foreach ($requestLogMessages as $messageData) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData($messageData['text'], $messageData['type']);

            $messages[] = $message;
        }

        $messages = array_merge($messages, $this->getResponse()->getMessages()->getEntities());

        $this->processMessages($messages);
    }

    protected function processResponseData()
    {
        if ($this->getResponse()->isResultError()) {
            return;
        }

        $responseData = $this->getPreparedResponseData();
        $responseMessages = $this->getResponse()->getMessages()->getEntities();

        $this->processCompleted(
            $responseData, array(
            'is_images_upload_error' => $this->isImagesUploadFailed($responseMessages)
            )
        );
    }

    protected function processMessages(array $messages)
    {
        foreach ($messages as $message) {
            $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);
        }
    }

    protected function processCompleted(array $data = array(), array $params = array())
    {
        $this->getResponseObject()->processSuccess($data, $params);

        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData(
            $this->getSuccessfulMessage(),
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS
        );

        $this->getLogger()->logListingProductMessage(
            $this->_listingProduct, $message
        );

        $this->_isSuccess = true;
    }

    //----------------------------------------

    abstract protected function getSuccessfulMessage();

    //########################################

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message[] $messages
     * @return Ess_M2ePro_Model_Connector_Connection_Response_Message|bool
     *
     * eBay internal error. The operation was not completed (code:34) (returned by M2e Pro server)
     */
    protected function isEbayApplicationErrorAppeared(array $messages)
    {
        foreach ($messages as $message) {
            if (strpos($message->getText(), 'code:34') !== false) {
                return $message;
            }
        }

        return false;
    }

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message[] $messages
     * @return Ess_M2ePro_Model_Connector_Connection_Response_Message|bool
     *
     * 32704531: Can't upload product image on eBay (returned by M2e Pro server)
     */
    protected function isImagesUploadFailed(array $messages)
    {
        foreach ($messages as $message) {
            if ($message->getCode() == 32704531) {
                return $message;
            }
        }

        return false;
    }

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message[] $messages
     * @return Ess_M2ePro_Model_Connector_Connection_Response_Message|bool
     *
     * 17: This item cannot be accessed because the listing has been deleted, is a Half.com listing,
     *     or you are not the seller.
     */
    protected function isItemCanNotBeAccessed(array $messages)
    {
        foreach ($messages as $message) {
            if ($message->getCode() == 17) {
                return $message;
            }
        }

        return false;
    }

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message[] $messages
     * @return Ess_M2ePro_Model_Connector_Connection_Response_Message|bool
     *
     * 21919301: (UPC/EAN/ISBN) is missing a value. Enter a value and try again.
     */
    protected function isNewRequiredSpecificNeeded(array $messages)
    {
        foreach ($messages as $message) {
            if ($message->getCode() == 21919301) {
                return $message;
            }
        }

        return false;
    }

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message[] $messages
     * @return Ess_M2ePro_Model_Connector_Connection_Response_Message|bool
     *
     * 21916587: The multi-variation titles have been changed and were not updated on the eBay.
     * 21916626: Variations Specifics and Item Specifics entered for a Multi-SKU item should be different.
     * 21916603: Variation specifics cannot be changed in restricted revise
     * 21916664: Variation Specifics provided does not match with the variation specifics of the variations on the item.
     * 21916585: Duplicate custom variation label.
     * 21916582: Duplicate VariationSpecifics trait value in the VariationSpecificsSet container.
     * 21916672: The tags (MPN) is/are disabled as Variant.
     * 21919061: This item was created from Selling Manager product, but the VariationSpecifics or V
     *           ariationSpecificsSet provided for this item does not match with the product.
     *           Please update variation specifics on the product and try again.
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
            21919061
        );

        foreach ($messages as $message) {
            if (in_array($message->getCode(), $errorCodes)) {
                return $message;
            }
        }

        return false;
    }

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message[] $messages
     * @return Ess_M2ePro_Model_Connector_Connection_Response_Message|bool
     *
     * 21916884: Condition is required for this category.
     */
    protected function isConditionErrorAppeared(array $messages)
    {
        foreach ($messages as $message) {
            if ($message->getCode() == 21916884) {
                return $message;
            }
        }

        return false;
    }

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message[] $messages
     * @return Ess_M2ePro_Model_Connector_Connection_Response_Message|bool
     *
     * 488: The specified UUID has already been used; ListedByRequestAppId=1, item ID=%ited_id%.
     */
    protected function isDuplicateErrorByUUIDAppeared(array $messages)
    {
        foreach ($messages as $message) {
            if ($message->getCode() == 488) {
                return $message;
            }
        }

        return false;
    }

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message[] $messages
     * @return Ess_M2ePro_Model_Connector_Connection_Response_Message|bool
     *
     * 21919067: This Listing is a duplicate of your item: %tem_title% (%item_id%).
     */
    protected function isDuplicateErrorByEbayEngineAppeared(array $messages)
    {
        foreach ($messages as $message) {
            if ($message->getCode() == 21919067) {
                return $message;
            }
        }

        return false;
    }

    //########################################

    //TODO: Improve this functionality so that is was able to fix some sort of variation product errors
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

        $this->_listingProduct->setSetting('additional_data', 'is_variation_mpn_filled', $isVariationMpnFilled);
        if (!$isVariationMpnFilled) {
            $this->_listingProduct->setSetting('additional_data', 'without_mpn_variation_issue', true);
        }

        $this->_listingProduct->save();

        if (!empty($variationMpnValues)) {
            $this->fillVariationMpnValues($variationMpnValues);
        }

        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData(
            Mage::helper('M2ePro')->__(
                'It has been detected that this Item failed to be updated on eBay because of the errors.
                M2E Pro will automatically try to apply another solution to Revise this Item.'
            ),
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
        );

        $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);

        $this->processAdditionalAction($this->getActionType(), $this->getConfigurator());
    }

    protected function canPerformGetItemCall()
    {
        if ($this->getStatusChanger() == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            return true;
        }

        $getItemCallsCount   = 0;
        $getItemLastCallDate = null;

        $maxAllowedGetItemCallsCount = 2;

        $additionalData = $this->_listingProduct->getAdditionalData();
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

        $this->_listingProduct->setSettings('additional_data', $additionalData);
        $this->_listingProduct->save();

        return true;
    }

    protected function getVariationMpnDataFromEbay()
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $this->_listingProduct->getChildObject();

        /** @var Ess_M2ePro_Model_Connector_Command_RealTime_Virtual $connector */
        $connector = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher')->getVirtualConnector(
            'item', 'get', 'info',
            array(
                'item_id' => $ebayListingProduct->getEbayItemIdReal(),
                'parser_type' => 'standard',
                'full_variations_mode' => true
            ), 'result', $this->getMarketplace(), $this->getAccount()
        );

        try {
            $connector->process();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }

        $itemData = $connector->getResponseData();
        if (empty($itemData['variations'])) {
            return array();
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
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Variation_Collection $variationCollection */
        $variationCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product_Variation');
        $variationCollection->addFieldToFilter('listing_product_id', $this->_listingProduct->getId());

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Variation_Option_Collection $variationOptionCollection */
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

                if (!isset($additionalData['online_product_details']['mpn']) ||
                    $additionalData['online_product_details']['mpn'] != $variationMpnValue['mpn']
                ) {
                    $additionalData['online_product_details']['mpn'] = $variationMpnValue['mpn'];

                    $variation->setSettings('additional_data', $additionalData);
                    $variation->save();
                }

                unset($variationMpnValues[$id]);

                break;
            }
        }
    }

    protected function processDuplicateByUUID(Ess_M2ePro_Model_Connector_Connection_Response_Message $message)
    {
        $duplicateItemId = null;
        preg_match('/item\s*ID=(?<itemId>\d+)\.$/i', $message->getText(), $matches);
        if (!empty($matches['itemId'])) {
            $duplicateItemId = $matches['itemId'];
        }

        $this->_listingProduct->setData('is_duplicate', 1);
        $this->_listingProduct->setSetting(
            'additional_data', 'item_duplicate_action_required', array(
            'item_id' => $duplicateItemId,
            'source'  => 'uuid',
            'message' => $message->getText()
            )
        );
        $this->_listingProduct->save();
    }

    protected function processDuplicateByEbayEngine(Ess_M2ePro_Model_Connector_Connection_Response_Message $message)
    {
        $duplicateItemId = null;
        preg_match('/.*\((\d+)\)/', $message->getText(), $matches);
        if (!empty($matches[1])) {
            $duplicateItemId = $matches[1];
        }

        $this->_listingProduct->setData('is_duplicate', 1);
        $this->_listingProduct->setSetting(
            'additional_data', 'item_duplicate_action_required', array(
            'item_id' => $duplicateItemId,
            'source'  => 'ebay_engine',
            'message' => $message->getText()
            )
        );
        $this->_listingProduct->save();
    }

    //########################################

    protected function getConfigurator()
    {
        if (empty($this->_configurator)) {
            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            $configurator->setData($this->_params['product']['configurator']);

            $this->_configurator = $configurator;
        }

        return $this->_configurator;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
     */
    protected function getResponseObject()
    {
        if (empty($this->_responseObject)) {
            /** @var $response Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response */
            $response = Mage::getModel(
                'M2ePro/Ebay_Listing_Product_Action_Type_'.$this->getOrmActionType().'_Response'
            );

            $response->setParams($this->_params['params']);
            $response->setListingProduct($this->_listingProduct);
            $response->setConfigurator($this->getConfigurator());
            $response->setRequestData($this->getRequestDataObject());

            $requestMetaData = !empty($this->_params['product']['request_metadata'])
                ? $this->_params['product']['request_metadata'] : array();

            $response->setRequestMetaData($requestMetaData);

            $this->_responseObject = $response;
        }

        return $this->_responseObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function getRequestDataObject()
    {
        if (empty($this->_requestDataObject)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData $requestData */
            $requestData = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_RequestData');

            $requestData->setData($this->_params['product']['request']);
            $requestData->setListingProduct($this->_listingProduct);

            $this->_requestDataObject = $requestData;
        }

        return $this->_requestDataObject;
    }

    //########################################

    protected function markAsPotentialDuplicate()
    {
        $additionalData = $this->_listingProduct->getAdditionalData();

        $additionalData['last_failed_action_data'] = array(
            'native_request_data' => $this->getRequestDataObject()->getData(),
            'previous_status' => $this->_listingProduct->getStatus(),
            'action' => $this->getActionType(),
            'request_time' => $this->getResponse()->getRequestTime(),
        );

        $this->_listingProduct->addData(
            array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
            'additional_data' => Mage::helper('M2ePro')->jsonEncode($additionalData),
            )
        )->save();

        $this->_listingProduct->getChildObject()->updateVariationsStatus();
    }

    //########################################

    protected function processAdditionalAction(
        $actionType,
        Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $configurator,
        array $params = array()
    ) {
        $listingProduct = clone $this->_listingProduct;
        $listingProduct->setActionConfigurator($configurator);

        $params = array_merge(
            $params,
            array(
                'status_changer' => $this->getStatusChanger(),
                'is_realtime'    => !empty($this->_params['is_realtime']),
            )
        );

        $dispatcher = Mage::getModel('M2ePro/Ebay_Connector_Item_Dispatcher');
        $dispatcher->process($actionType, array($listingProduct), $params);

        $logsActionId = $this->_params['logs_action_id'];
        if (!is_array($logsActionId)) {
            $logsActionId = array($logsActionId);
        }

        $logsActionId[] = $dispatcher->getLogsActionId();

        $this->_params['logs_action_id'] = $logsActionId;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getLogger()
    {
        if ($this->_logger === null) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger $logger */

            $logger = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Logger');

            if (!isset($this->_params['logs_action_id']) || !isset($this->_params['status_changer'])) {
                throw new Ess_M2ePro_Model_Exception('Product Connector has not received some params');
            }

            $logger->setActionId((int)$this->_params['logs_action_id']);
            $logger->setAction($this->getLogsAction());

            switch ($this->_params['status_changer']) {
                case Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN:
                    $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;
                    break;
                case Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER:
                    $initiator = Ess_M2ePro_Helper_Data::INITIATOR_USER;
                    break;
                default:
                    $initiator = Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION;
                    break;
            }

            $logger->setInitiator($initiator);

            $this->_logger = $logger;
        }

        return $this->_logger;
    }

    //########################################

    public function getStatus()
    {
        return $this->getLogger()->getStatus();
    }

    public function getLogsActionId()
    {
        return $this->_params['logs_action_id'];
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account', 'account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getObjectByParam('Account', 'marketplace_id');
    }

    //---------------------------------------

    protected function getActionType()
    {
        return $this->_params['action_type'];
    }

    protected function getLockIdentifier()
    {
        return $this->_params['lock_identifier'];
    }

    //---------------------------------------

    protected function getLogsAction()
    {
        return $this->_params['logs_action'];
    }

    //---------------------------------------

    protected function getStatusChanger()
    {
        return (int)$this->_params['status_changer'];
    }

    //########################################

    protected function getOrmActionType()
    {
        switch ($this->getActionType()) {
            case Ess_M2ePro_Model_Listing_Product::ACTION_LIST:
                return 'List';
            case Ess_M2ePro_Model_Listing_Product::ACTION_RELIST:
                return 'Relist';
            case Ess_M2ePro_Model_Listing_Product::ACTION_REVISE:
                return 'Revise';
            case Ess_M2ePro_Model_Listing_Product::ACTION_STOP:
                return 'Stop';
        }

        throw new Ess_M2ePro_Model_Exception('Wrong Action type');
    }

    //########################################
}