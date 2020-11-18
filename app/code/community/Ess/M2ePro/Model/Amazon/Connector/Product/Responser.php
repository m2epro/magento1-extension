<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Connector_Product_Responser
    extends Ess_M2ePro_Model_Connector_Command_Pending_Responser
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product $_listingProduct
     */
    protected $_listingProduct = null;

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Logger
     */
    protected $_logger = null;

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator $_configurator
     */
    protected $_configurator = null;

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response $_responseObject
     */
    protected $_responseObject = null;

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData $_requestDataObject
     */
    protected $_requestDataObject = null;

    protected $_isSuccess = false;

    //########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Connector_Connection_Response $response)
    {
        parent::__construct($params, $response);

        $this->_listingProduct = Mage::helper('M2ePro/Component_Amazon')
                                     ->getObject('Listing_Product', $this->_params['product']['id']);
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
            $message
        );
    }

    public function eventAfterExecuting()
    {
        if ($this->isTemporaryErrorAppeared($this->getResponse()->getMessages()->getEntities())) {
            $this->getResponseObject()->throwRepeatActionInstructions();
        }

        parent::eventAfterExecuting();
        $this->processParentProcessor();
    }

    protected function processParentProcessor()
    {
        if (!$this->_isSuccess) {
            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->_listingProduct->getChildObject();

        $variationManager = $amazonListingProduct->getVariationManager();

        if (!$variationManager->isRelationMode()) {
            return;
        }

        if ($variationManager->isRelationParentType()) {
            $parentListingProduct = $this->_listingProduct;
        } else {
            $parentListingProduct = $variationManager->getTypeModel()->getParentListingProduct();
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonParentListingProduct */
        $amazonParentListingProduct = $parentListingProduct->getChildObject();

        $parentTypeModel = $amazonParentListingProduct->getVariationManager()->getTypeModel();
        $parentTypeModel->getProcessor()->process();
    }

    //########################################

    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();
        return isset($responseData['messages']) && is_array($responseData['messages']);
    }

    protected function processResponseData()
    {
        $messages = array();

        $responseData = $this->getPreparedResponseData();

        $requestLogMessages = isset($this->_params['product']['request_metadata']['log_messages'])
            ? $this->_params['product']['request_metadata']['log_messages'] : array();

        foreach ($requestLogMessages as $messageData) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData($messageData['text'], $messageData['type']);

            $messages[] = $message;
        }

        foreach ($responseData['messages'] as $messageData) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromResponseData($messageData);

            $messages[] = $message;
        }

        if (!$this->processMessages($messages)) {
            return;
        }

        $successParams = $this->getSuccessfulParams();
        $this->processSuccess($successParams);
    }

    //----------------------------------------

    protected function processMessages(array $messages)
    {
        $hasError = false;

        foreach ($messages as $message) {
            /** @var Ess_M2ePro_Model_Connector_Connection_Response_Message $message */

            !$hasError && $hasError = $message->isError();
            $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);
        }

        return !$hasError;
    }

    protected function processSuccess(array $params = array())
    {
        $this->getResponseObject()->processSuccess($params);

        $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
        $message->initFromPreparedData(
            $this->getSuccessfulMessage(),
            Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_SUCCESS
        );

        if ($message->getText() !== null) {
            $this->getLogger()->logListingProductMessage($this->_listingProduct, $message);
        }

        $this->_isSuccess = true;
    }

    //----------------------------------------

    protected function getSuccessfulParams()
    {
        return array();
    }

    //----------------------------------------

    /**
     * @return string
     */
    abstract protected function getSuccessfulMessage();

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Logger
     */
    protected function getLogger()
    {
        if ($this->_logger === null) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Logger $logger */

            $logger = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Logger');

            $logger->setActionId($this->getLogsActionId());
            $logger->setAction($this->getLogsAction());

            switch ($this->getStatusChanger()) {
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

    protected function getConfigurator()
    {
        if ($this->_configurator === null) {
            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');
            $configurator->setData($this->_params['product']['configurator']);

            $this->_configurator = $configurator;
        }

        return $this->_configurator;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response
     */
    protected function getResponseObject()
    {
        if ($this->_responseObject === null) {
            /** @var $response Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response */
            $response = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_'.$this->getOrmActionType().'_Response'
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
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData
     */
    protected function getRequestDataObject()
    {
        if ($this->_requestDataObject === null) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData $requestData */
            $requestData = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_RequestData');

            $requestData->setData($this->_params['product']['request']);
            $requestData->setListingProduct($this->_listingProduct);

            $this->_requestDataObject = $requestData;
        }

        return $this->_requestDataObject;
    }

    //########################################

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

    protected function getLogsActionId()
    {
        return (int)$this->_params['logs_action_id'];
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
            case Ess_M2ePro_Model_Listing_Product::ACTION_DELETE:
                return 'Delete';
        }

        throw new Ess_M2ePro_Model_Exception('Wrong Action type');
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message[] $messages
     * @return Ess_M2ePro_Model_Connector_Connection_Response_Message|bool
     *
     * TODO ERROR CODEs
     */
    protected function isTemporaryErrorAppeared(array $messages)
    {
        $errorCodes = array(
            /* TODO ERROR CODEs */
        );

        foreach ($messages as $message) {
            if (in_array($message->getCode(), $errorCodes, true)) {
                return $message;
            }
        }

        return false;
    }

    //########################################
}
