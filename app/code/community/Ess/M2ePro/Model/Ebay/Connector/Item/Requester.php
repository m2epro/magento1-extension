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

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $listingProduct = NULL;

    /** @var Ess_M2ePro_Model_Listing_Product_LockManager */
    protected $lockManager = NULL;

    /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Validator $validatorObject */
    protected $validatorObject = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
     */
    protected $requestObject = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected $requestDataObject = NULL;

    /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger $logger */
    protected $logger = NULL;

    /**
     * @var Ess_M2ePro_Model_Connector_Connection_Response_Message[]
     */
    protected $storedLogMessages = array();

    protected $isRealTime = false;

    //########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;

        if (is_null($this->listingProduct->getActionConfigurator())) {
            $this->listingProduct->setActionConfigurator(
                Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator')
            );
        }

        $this->marketplace = $this->listingProduct->getMarketplace();
        $this->account     = $this->listingProduct->getAccount();
    }

    //########################################

    public function setIsRealTime($isRealTime = true)
    {
        $this->isRealTime = $isRealTime;
        return $this;
    }

    public function isRealTime()
    {
        return $this->isRealTime;
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
                'listing_product_id'  => $this->listingProduct->getId(),
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
            (is_null($requestData['is_eps_ebay_images_mode']) &&
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
        try {
            parent::process();
        } catch (Ess_M2ePro_Model_Exception_Connection $exception) {
            if ($this->account->getChildObject()->isModeSandbox()) {
                throw $exception;
            }

            $this->processResponser();

        } catch (Exception $exception) {
            if (strpos($exception->getMessage(), 'code:34') === false ||
                $this->account->getChildObject()->isModeSandbox()
            ) {
                throw $exception;
            }

            $this->processResponser();
        }

        if ($this->getResponser()->getStatus() != Ess_M2ePro_Helper_Data::STATUS_SUCCESS) {
            $this->getLogger()->setStatus($this->getResponser()->getStatus());
        }
        $this->params['logs_action_id'] = $this->getResponser()->getLogsActionId();
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
            'configurator'     => $this->listingProduct->getActionConfigurator()->getData(),
            'id'               => $this->listingProduct->getId(),
        );

        return array(
            'is_realtime'     => $this->isRealTime(),
            'account_id'      => $this->account->getId(),
            'action_type'     => $this->getActionType(),
            'lock_identifier' => $this->getLockIdentifier(),
            'logs_action'     => $this->getLogsAction(),
            'logs_action_id'  => $this->getLogger()->getActionId(),
            'status_changer'  => $this->params['status_changer'],
            'params'          => $this->params,
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
        $configurator = $this->listingProduct->getActionConfigurator();
        if (count($configurator->getAllowedDataTypes()) <= 0) {

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
        $variationUpdater->process($this->listingProduct);
        $variationUpdater->afterMassProcessEvent();
    }

    //########################################

    protected function isListingProductLocked()
    {
        if ($this->listingProduct->isSetProcessingLock('in_action') || $this->getLockManager()->isLocked()) {

            // M2ePro_TRANSLATIONS
            // Another Action is being processed. Try again when the Action is completed.
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

    // ########################################

    protected function lockListingProduct()
    {
        $this->getLockManager()->lock();
    }

    protected function unlockListingProduct()
    {
        $this->getLockManager()->unlock();
    }

    // ########################################

    public function getStatus()
    {
        return $this->getLogger()->getStatus();
    }

    /**
     * @return array|integer
     */
    public function getLogsActionId()
    {
        return $this->params['logs_action_id'];
    }

    //########################################

    abstract protected function getActionType();

    abstract protected function getLogsAction();

    protected function getLockManager()
    {
        if (!is_null($this->lockManager)) {
            return $this->lockManager;
        }

        switch ($this->params['status_changer']) {
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

        $this->lockManager = Mage::getModel(
            'M2ePro/Listing_Product_LockManager', array('listing_product' => $this->listingProduct)
        );
        $this->lockManager->setInitiator($initiator);
        $this->lockManager->setLogsActionId($this->params['logs_action_id']);
        $this->lockManager->setLogsAction($this->getLogsAction());

        return $this->lockManager;
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
        if (empty($this->validatorObject)) {

            /** @var $validator Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Validator */
            $validator = Mage::getModel(
                'M2ePro/Ebay_Listing_Product_Action_Type_'.$this->getOrmActionType().'_Validator'
            );

            $validator->setParams($this->params);
            $validator->setListingProduct($this->listingProduct);
            $validator->setConfigurator($this->listingProduct->getActionConfigurator());

            $this->validatorObject = $validator;
        }

        return $this->validatorObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
     */
    protected function getRequestObject()
    {
        if (empty($this->requestObject)) {
            $this->requestObject = $this->makeRequestObject();
        }

        return $this->requestObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
     */
    protected function makeRequestObject()
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request $request */

        $request = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Type_'.$this->getOrmActionType().'_Request');

        $request->setParams($this->params);
        $request->setListingProduct($this->listingProduct);
        $request->setConfigurator($this->listingProduct->getActionConfigurator());
        $request->setCachedData($this->getValidatorObject()->getData());

        return $request;
    }

    /**
     * @param array $data
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function buildRequestDataObject(array $data)
    {
        if (empty($this->requestDataObject)) {
            $this->requestDataObject = $this->makeRequestDataObject($data);
        }

        return $this->requestDataObject;
    }

    /**
     * @param array $data
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function makeRequestDataObject(array $data)
    {
        $requestData = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_RequestData');

        $requestData->setData($data);
        $requestData->setListingProduct($this->listingProduct);

        return $requestData;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function getRequestDataObject()
    {
        return $this->requestDataObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getLogger()
    {
        if (is_null($this->logger)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger $logger */

            $logger = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Logger');

            if (!isset($this->params['logs_action_id']) || !isset($this->params['status_changer'])) {
                throw new Ess_M2ePro_Model_Exception('Product Connector has not received some params');
            }

            $logger->setActionId((int)$this->params['logs_action_id']);
            $logger->setAction($this->getLogsAction());

            switch ($this->params['status_changer']) {
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

            $this->logger = $logger;
        }

        return $this->logger;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Connector_Connection_Response_Message[]
     */
    protected function getStoredLogMessages()
    {
        return $this->storedLogMessages;
    }

    protected function storeLogMessage(Ess_M2ePro_Model_Connector_Connection_Response_Message $message)
    {
        $this->storedLogMessages[] = $message;
    }

    protected function writeStoredLogMessages()
    {
        foreach ($this->getStoredLogMessages() as $message) {
            $this->getLogger()->logListingProductMessage(
                $this->listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );
        }
    }

    //########################################
}