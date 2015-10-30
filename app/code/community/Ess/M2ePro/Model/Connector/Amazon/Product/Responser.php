<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Amazon_Product_Responser
    extends Ess_M2ePro_Model_Connector_Amazon_Responser
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product[]
     */
    protected $listingsProducts = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product[]
     */
    protected $successfulListingProducts = array();

    // ---------------------------------------

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Logger
     */
    protected $logger = NULL;

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator[]
     */
    protected $configurators = array();

    // ---------------------------------------

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response[]
     */
    protected $responsesObjects = array();

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData[]
     */
    protected $requestsDataObjects = array();

    protected $isResponseFailed = false;

    //########################################

    public function __construct(array $params = array())
    {
        parent::__construct($params);

        foreach ($this->params['products'] as $id => $listingProductData) {
            try {
                $this->listingsProducts[] = Mage::helper('M2ePro/Component_Amazon')
                                                    ->getObject('Listing_Product',$id);
            } catch (Exception $exception) {}
        }
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        $identifier = $this->getLockIdentifier();

        $tempListings = array();
        $tempParentListingProducts = array();
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            $listingProduct->deleteObjectLocks(NULL, $processingRequest->getHash());
            $listingProduct->deleteObjectLocks('in_action', $processingRequest->getHash());
            $listingProduct->deleteObjectLocks($identifier.'_action', $processingRequest->getHash());

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            $variationManager = $amazonListingProduct->getVariationManager();

            if ($variationManager->isRelationChildType()) {

                /** @var Ess_M2ePro_Model_Amazon_Listing_Product $parentListingProduct */
                $parentListingProduct = $variationManager->getTypeModel()->getParentListingProduct();
                if (isset($tempParentListingProducts[$parentListingProduct->getId()])) {
                    continue;
                }

                $parentListingProduct->deleteObjectLocks(NULL, $processingRequest->getHash());
                $parentListingProduct->deleteObjectLocks('child_products_in_action', $processingRequest->getHash());

                $tempParentListingProducts[$parentListingProduct->getId()] = true;
            }

            if (isset($tempListings[$listingProduct->getListingId()])) {
                continue;
            }

            $listingProduct->getListing()->deleteObjectLocks(NULL, $processingRequest->getHash());

            $tempListings[$listingProduct->getListingId()] = true;
        }
    }

    public function eventFailedExecuting($message)
    {
        parent::eventFailedExecuting($message);

        $this->isResponseFailed = true;

        foreach ($this->listingsProducts as $listingProduct) {
            $this->getLogger()->logListingProductMessage(
                $listingProduct,
                $message,
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }
    }

    public function eventAfterExecuting()
    {
        parent::eventAfterExecuting();

        if (!$this->isResponseFailed) {
            $this->processParentProcessors();
            $this->inspectProducts();
        }
    }

    protected function inspectProducts()
    {
        $listingsProductsByStatus = array(
            Ess_M2ePro_Model_Listing_Product::STATUS_LISTED  => array(),
            Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED => array(),
            Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN => array(),
        );

        foreach ($this->successfulListingProducts as $listingProduct) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            if ($amazonListingProduct->getVariationManager()->isRelationParentType()) {
                continue;
            }

            $listingsProductsByStatus[$listingProduct->getStatus()][$listingProduct->getId()] = $listingProduct;
        }

        $runner = Mage::getModel('M2ePro/Synchronization_Templates_Runner');
        $runner->setConnectorModel('Connector_Amazon_Product_Dispatcher');
        $runner->setMaxProductsPerStep(100);

        $inspector = Mage::getModel('M2ePro/Amazon_Synchronization_Templates_Inspector');

        foreach ($listingsProductsByStatus[Ess_M2ePro_Model_Listing_Product::STATUS_LISTED] as $listingProduct) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');

            if ($inspector->isMeetStopRequirements($listingProduct)) {
                $runner->addProduct(
                    $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_STOP, $configurator
                );

                continue;
            }

            $configurator->setPartialMode();

            $needRevise = false;

            if ($inspector->isMeetReviseQtyRequirements($listingProduct)) {
                $configurator->allowQty();
                $needRevise = true;
            }

            if ($inspector->isMeetRevisePriceRequirements($listingProduct)) {
                $configurator->allowPrice();
                $needRevise = true;
            }

            if (!$needRevise) {
                continue;
            }

            $runner->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        foreach ($listingsProductsByStatus[Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED] as $listingProduct) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            if (!$inspector->isMeetRelistRequirements($listingProduct)) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            $synchronizationTemplate = $amazonListingProduct->getAmazonSynchronizationTemplate();

            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');

            if (!$synchronizationTemplate->isRelistSendData()) {
                $configurator->setPartialMode();
                $configurator->allowQty();
            }

            $runner->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_RELIST, $configurator
            );
        }

        foreach ($listingsProductsByStatus[Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN] as $listingProduct) {

            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */

            if (!$inspector->isMeetRevisePriceRequirements($listingProduct)) {
                continue;
            }

            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');
            $configurator->setPartialMode();
            $configurator->allowPrice();

            $runner->addProduct(
                $listingProduct, Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $configurator
            );
        }

        $runner->execute();
    }

    protected function processParentProcessors()
    {
        $processedParentListingProducts = array();

        foreach ($this->successfulListingProducts as $listingProduct) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            $variationManager = $amazonListingProduct->getVariationManager();

            if (!$variationManager->isRelationMode()) {
                continue;
            }

            if ($variationManager->isRelationParentType()) {
                $parentListingProduct = $listingProduct;
            } else {
                $parentListingProduct = $variationManager->getTypeModel()->getParentListingProduct();
            }

            if (isset($processedParentListingProducts[$parentListingProduct->getId()])) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonParentListingProduct */
            $amazonParentListingProduct = $parentListingProduct->getChildObject();

            $parentTypeModel = $amazonParentListingProduct->getVariationManager()->getTypeModel();
            $parentTypeModel->getProcessor()->process();

            $processedParentListingProducts[$parentListingProduct->getId()] = true;
        }
    }

    //########################################

    protected function processResponseMessages(array $messages = array())
    {
        parent::processResponseMessages($messages);

        foreach ($this->listingsProducts as $listingProduct) {
            $this->processMessages($listingProduct, $this->messages);
        }
    }

    protected function isNeedToParseResponseData($responseBody)
    {
        if (!parent::isNeedToParseResponseData($responseBody)) {
            return false;
        }

        if ($this->hasErrorMessages() && !isset($responseBody['messages'])) {
            return false;
        }

        return true;
    }

    //########################################

    protected function validateResponseData($response)
    {
        return isset($response['messages']) && is_array($response['messages']);
    }

    protected function processResponseData($response)
    {
        $responseMessages = array();

        foreach ($response['messages'] as $key => $value) {
            $responseMessages[(int)$key] = $value;
        }

        $globalMessages = array();

        if (isset($responseMessages[0]) && is_array($responseMessages[0])) {
            $globalMessages = $responseMessages[0];
            unset($responseMessages[0]);
        }

        foreach ($this->listingsProducts as $listingProduct) {

            $messages = $globalMessages;

            if (isset($responseMessages[(int)$listingProduct->getId()]) &&
                is_array($responseMessages[(int)$listingProduct->getId()])) {
                $messages = array_merge($globalMessages, $responseMessages[(int)$listingProduct->getId()]);
            }

            if (!$this->processMessages($listingProduct, $messages) || $this->hasErrorMessages()) {
                continue;
            }

            $successParams = $this->getSuccessfulParams($listingProduct,$response);
            $this->processSuccess($listingProduct, $successParams);
        }
    }

    // ---------------------------------------

    protected function processMessages(Ess_M2ePro_Model_Listing_Product $listingProduct, array $messages = array())
    {
        $hasError = false;

        foreach ($messages as $message) {

            $messageData = $this->getLogger()->getConvertedMessageData($message);
            !$hasError && $hasError = ($messageData['type'] == Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR);

            $this->getLogger()->logListingProductMessage(
                $listingProduct,
                $messageData['text'],
                $messageData['type'],
                $messageData['priority']
            );
        }

        return !$hasError;
    }

    protected function processSuccess(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        $this->getResponseObject($listingProduct)->processSuccess($params);

        $this->getLogger()->logListingProductMessage(
            $listingProduct,
            $this->getSuccessfulMessage($listingProduct),
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );

        $this->successfulListingProducts[$listingProduct->getId()] = $listingProduct;
    }

    // ---------------------------------------

    protected function getSuccessfulParams(Ess_M2ePro_Model_Listing_Product $listingProduct, $response)
    {
        return array();
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return string
     */
    abstract protected function getSuccessfulMessage(Ess_M2ePro_Model_Listing_Product $listingProduct);

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Logger
     */
    protected function getLogger()
    {
        if (is_null($this->logger)) {

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

            $this->logger = $logger;
        }

        return $this->logger;
    }

    protected function getConfigurator(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (empty($this->configurators[$listingProduct->getId()])) {

            $configurator = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Configurator');
            $configurator->setData($this->params['products'][$listingProduct->getId()]['configurator']);

            $this->configurators[$listingProduct->getId()] = $configurator;
        }

        return $this->configurators[$listingProduct->getId()];
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response
     */
    protected function getResponseObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!isset($this->responsesObjects[$listingProduct->getId()])) {

            /* @var $response Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response */
            $response = Mage::getModel(
                'M2ePro/Amazon_Listing_Product_Action_Type_'.$this->getOrmActionType().'_Response'
            );

            $response->setParams($this->params['params']);
            $response->setListingProduct($listingProduct);
            $response->setConfigurator($this->getConfigurator($listingProduct));
            $response->setRequestData($this->getRequestDataObject($listingProduct));

            $this->responsesObjects[$listingProduct->getId()] = $response;
        }

        return $this->responsesObjects[$listingProduct->getId()];
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData
     */
    protected function getRequestDataObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!isset($this->requestsDataObjects[$listingProduct->getId()])) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData $requestData */
            $requestData = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_RequestData');

            $requestData->setData($this->params['products'][$listingProduct->getId()]['request']);
            $requestData->setListingProduct($listingProduct);

            $this->requestsDataObjects[$listingProduct->getId()] = $requestData;
        }

        return $this->requestsDataObjects[$listingProduct->getId()];
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getAccount()->getChildObject()->getMarketplace();
    }

    // ---------------------------------------

    protected function getActionType()
    {
        return $this->params['action_type'];
    }

    protected function getLockIdentifier()
    {
        return $this->params['lock_identifier'];
    }

    // ---------------------------------------

    protected function getLogsAction()
    {
        return $this->params['logs_action'];
    }

    /**
     * @return int
     */
    protected function getLogsActionId()
    {
        return (int)$this->params['logs_action_id'];
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function getStatusChanger()
    {
        return (int)$this->params['status_changer'];
    }

    //########################################

    /**
     * @return string
     * @throws Ess_M2ePro_Model_Exception
     */
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
}