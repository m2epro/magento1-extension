<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Connector_Item_Requester
    extends Ess_M2ePro_Model_Ebay_Connector_Command_Pending_Requester
{
    const DEFAULT_REQUEST_TIMEOUT         = 300;
    const TIMEOUT_INCREMENT_FOR_ONE_IMAGE = 30;

    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct = null;

    /** @var Ess_M2ePro_Model_Listing_Product_LockManager */
    protected $_lockManager = null;

    /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Validator $_validatorObject */
    protected $_validatorObject = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
     */
    protected $_requestObject = null;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected $_requestDataObject = null;

    /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger $_logger */
    protected $_logger = null;

    /**
     * @var Ess_M2ePro_Model_Connector_Connection_Response_Message[]
     */
    protected $_storedLogMessages = array();

    protected $_isRealTime = false;

    //########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->_listingProduct = $listingProduct;

        if ($this->_listingProduct->getActionConfigurator() === null) {
            $this->_listingProduct->setActionConfigurator(
                Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator')
            );
        }

        $this->marketplace = $this->_listingProduct->getMarketplace();
        $this->account     = $this->_listingProduct->getAccount();
    }

    //########################################

    public function setIsRealTime($isRealTime = true)
    {
        $this->_isRealTime = $isRealTime;
        return $this;
    }

    public function isRealTime()
    {
        return $this->_isRealTime;
    }

    //########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Ebay_Connector_Item_ProcessingRunner';
    }

    protected function getProcessingParams()
    {
        return array_merge(
            parent::getProcessingParams(),
            array(
                'request_data'        => $this->getRequestData(),
                'listing_product_id'  => $this->_listingProduct->getId(),
                'lock_identifier'     => $this->getLockIdentifier(),
                'action_type'         => $this->getActionType(),
                'request_timeout'     => $this->getRequestTimeout(),
            )
        );
    }

    //########################################

    protected function buildConnectionInstance()
    {
        $connection = parent::buildConnectionInstance();
        $connection->setTimeout($this->getRequestTimeOut());

        return $connection;
    }

    public function getRequestTimeout()
    {
        $requestDataObject = $this->getRequestDataObject();
        $requestData = $requestDataObject->getData();

        if (!isset($requestData['is_eps_ebay_images_mode']) || !isset($requestData['upload_images_mode']) ||
            $requestData['is_eps_ebay_images_mode'] === false ||
            ($requestData['is_eps_ebay_images_mode'] === null &&
                $requestData['upload_images_mode'] ==
                Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Images::UPLOAD_IMAGES_MODE_SELF)) {
            return self::DEFAULT_REQUEST_TIMEOUT;
        }

        $imagesTimeout = self::TIMEOUT_INCREMENT_FOR_ONE_IMAGE * $requestDataObject->getTotalImagesCount();
        return self::DEFAULT_REQUEST_TIMEOUT + $imagesTimeout;
    }

    //########################################

    public function process()
    {
        try {
            $this->getLogger()->setStatus(Ess_M2ePro_Helper_Data::STATUS_SUCCESS);

            if ($this->isListingProductLocked()) {
                $this->writeStoredLogMessages();
                return;
            }

            $this->lockListingProduct();
            $this->initializeVariations();

            if (!$this->validateListingProduct() || !$this->validateConfigurator()) {
                $this->writeStoredLogMessages();
                return;
            }

            if ($this->isRealTime()) {
                $this->processRealTime();
                return;
            }

            $this->eventBeforeExecuting();
            $this->getProcessingRunner()->start();
        } catch (Exception $exception) {
            $this->unlockListingProduct();
            throw $exception;
        }

        $this->unlockListingProduct();
    }

    protected function processRealTime()
    {
        parent::process();

        if ($this->getResponser()->getStatus() != Ess_M2ePro_Helper_Data::STATUS_SUCCESS) {
            $this->getLogger()->setStatus($this->getResponser()->getStatus());
        }

        $this->_params['logs_action_id'] = $this->getResponser()->getLogsActionId();
    }

    protected function processResponser()
    {
        $this->unlockListingProduct();
        parent::processResponser();
    }

    //########################################

    public function getRequestData()
    {
        $tempData = $this->getRequestObject()->getData();

        foreach ($this->getRequestObject()->getWarningMessages() as $messageText) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                $messageText, Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_WARNING
            );

            $this->storeLogMessage($message);
        }

        return $this->buildRequestDataObject($tempData)->getData();
    }

    //########################################

    protected function getResponserParams()
    {
        $logMessages = array();
        foreach ($this->getStoredLogMessages() as $message) {
            $logMessages[] = $message->asArray();
        }

        $metaData = $this->getRequestObject()->getMetaData();
        $metaData['log_messages'] = $logMessages;

        $product = array(
            'request'          => $this->getRequestDataObject()->getData(),
            'request_metadata' => $metaData,
            'configurator'     => $this->_listingProduct->getActionConfigurator()->getData(),
            'id'               => $this->_listingProduct->getId(),
        );

        return array(
            'is_realtime'     => $this->isRealTime(),
            'account_id'      => $this->account->getId(),
            'action_type'     => $this->getActionType(),
            'lock_identifier' => $this->getLockIdentifier(),
            'logs_action'     => $this->getLogsAction(),
            'logs_action_id'  => $this->getLogger()->getActionId(),
            'status_changer'  => $this->_params['status_changer'],
            'params'          => $this->_params,
            'product'         => $product,
        );
    }

    //########################################

    protected function validateListingProduct()
    {
        $validator = $this->getValidatorObject();

        $validationResult = $validator->validate();

        foreach ($validator->getMessages() as $messageData) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData($messageData['text'], $messageData['type']);

            $this->storeLogMessage($message);
        }

        if ($validationResult) {
            return true;
        }

        $this->unlockListingProduct();

        return false;
    }

    /**
     * Some data parts can be disallowed from configurator on validateListingProduct() action
     * @return bool
     */
    protected function validateConfigurator()
    {
        /** @var Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator */
        $configurator = $this->_listingProduct->getActionConfigurator();
        $types = $configurator->getAllowedDataTypes();

        if (empty($types)) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                'There was no need for this action. It was skipped.
                Please check the log message above for more detailed information.',
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
            );

            $this->storeLogMessage($message);
            $this->unlockListingProduct();

            return false;
        }

        return true;
    }

    //########################################

    protected function initializeVariations()
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Updater $variationUpdater */
        $variationUpdater = Mage::getModel('M2ePro/Ebay_Listing_Product_Variation_Updater');
        $variationUpdater->process($this->_listingProduct);
        $variationUpdater->afterMassProcessEvent();
    }

    //########################################

    protected function isListingProductLocked()
    {
        if ($this->_listingProduct->isSetProcessingLock('in_action') || $this->getLockManager()->isLocked()) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                'Another Action is being processed. Try again when the Action is completed.',
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
            );

            $this->storeLogMessage($message);

            return true;
        }

        return false;
    }

    //########################################

    protected function lockListingProduct()
    {
        $this->getLockManager()->lock();
    }

    protected function unlockListingProduct()
    {
        $this->getLockManager()->unlock();
    }

    //########################################

    public function getStatus()
    {
        return $this->getLogger()->getStatus();
    }

    /**
     * @return array|integer
     */
    public function getLogsActionId()
    {
        return $this->_params['logs_action_id'];
    }

    //########################################

    abstract protected function getActionType();

    abstract protected function getLogsAction();

    protected function getLockManager()
    {
        if ($this->_lockManager !== null) {
            return $this->_lockManager;
        }

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

        $this->_lockManager = Mage::getModel(
            'M2ePro/Listing_Product_LockManager', array('listing_product' => $this->_listingProduct)
        );
        $this->_lockManager->setInitiator($initiator);
        $this->_lockManager->setLogsActionId($this->_params['logs_action_id']);
        $this->_lockManager->setLogsAction($this->getLogsAction());

        return $this->_lockManager;
    }

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

    protected function getLockIdentifier()
    {
        return strtolower($this->getOrmActionType());
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Validator
     */
    protected function getValidatorObject()
    {
        if (empty($this->_validatorObject)) {

            /** @var $validator Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Validator */
            $validator = Mage::getModel(
                'M2ePro/Ebay_Listing_Product_Action_Type_'.$this->getOrmActionType().'_Validator'
            );

            $validator->setParams($this->_params);
            $validator->setListingProduct($this->_listingProduct);
            $validator->setConfigurator($this->_listingProduct->getActionConfigurator());

            $this->_validatorObject = $validator;
        }

        return $this->_validatorObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
     */
    protected function getRequestObject()
    {
        if (empty($this->_requestObject)) {
            $this->_requestObject = $this->makeRequestObject();
        }

        return $this->_requestObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
     */
    protected function makeRequestObject()
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request $request */

        $request = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Type_'.$this->getOrmActionType().'_Request');

        $request->setParams($this->_params);
        $request->setListingProduct($this->_listingProduct);
        $request->setConfigurator($this->_listingProduct->getActionConfigurator());
        $request->setCachedData($this->getValidatorObject()->getData());

        return $request;
    }

    /**
     * @param array $data
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function buildRequestDataObject(array $data)
    {
        if (empty($this->_requestDataObject)) {
            $this->_requestDataObject = $this->makeRequestDataObject($data);
        }

        return $this->_requestDataObject;
    }

    /**
     * @param array $data
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function makeRequestDataObject(array $data)
    {
        $requestData = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_RequestData');

        $requestData->setData($data);
        $requestData->setListingProduct($this->_listingProduct);

        return $requestData;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function getRequestDataObject()
    {
        return $this->_requestDataObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getLogger()
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

    /**
     * @return Ess_M2ePro_Model_Connector_Connection_Response_Message[]
     */
    protected function getStoredLogMessages()
    {
        return $this->_storedLogMessages;
    }

    protected function storeLogMessage(Ess_M2ePro_Model_Connector_Connection_Response_Message $message)
    {
        $this->_storedLogMessages[] = $message;
    }

    protected function writeStoredLogMessages()
    {
        foreach ($this->getStoredLogMessages() as $message) {
            $this->getLogger()->logListingProductMessage(
                $this->_listingProduct, $message
            );
        }
    }

    //########################################
}