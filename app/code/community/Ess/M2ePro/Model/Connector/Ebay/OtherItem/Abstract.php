<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Ebay_OtherItem_Abstract
    extends Ess_M2ePro_Model_Connector_Ebay_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing_Other
     */
    protected $otherListing = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Logger
     */
    protected $logger = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Request
     */
    protected $requestObject = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Response
     */
    protected $responseObject = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_RequestData
     */
    protected $requestDataObject = NULL;

    //########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Listing_Other $otherListing)
    {
        if (!is_null($otherListing->getActionConfigurator())) {
            $actionConfigurator = $otherListing->getActionConfigurator();
        } else {
            $actionConfigurator = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Configurator');
        }

        $this->otherListing = $otherListing->loadInstance($otherListing->getId());
        $this->otherListing->setActionConfigurator($actionConfigurator);

        parent::__construct($params, $this->otherListing->getMarketplace(), $this->otherListing->getAccount(), NULL);
    }

    //########################################

    public function process()
    {
        $this->getLogger()->setStatus(
            Ess_M2ePro_Helper_Data::STATUS_SUCCESS
        );

        if (!$this->isNeedSendRequest()) {
            return array();
        }

        $this->eventBeforeProcess();

        try {
            $result = parent::process();
        } catch (Exception $exception) {
            $this->eventAfterProcess();
            throw $exception;
        }

        $this->eventAfterProcess();

        foreach ($this->messages as $message) {

            $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;

            if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
            }

            $this->getLogger()->logListingOtherMessage($this->otherListing, $message, $priority);
        }

        return $result;
    }

    /**
     * @return int
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getStatus()
    {
        return $this->getLogger()->getStatus();
    }

    //########################################

    protected function isNeedSendRequest()
    {
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing_other_'.$this->otherListing->getId());

        if ($lockItem->isExist()) {

            $message = array(
                // M2ePro_TRANSLATIONS
                // Another Action is being processed. Try again when the Action is completed.
                parent::MESSAGE_TEXT_KEY => 'Another Action is being processed. '
                                           .'Try again when the Action is completed.',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->getLogger()->logListingOtherMessage($this->otherListing, $message,
                                                       Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            return false;
        }

        return $this->filterManualListingOther();
    }

    // ---------------------------------------

    abstract protected function filterManualListingOther();

    //########################################

    protected function eventBeforeProcess()
    {
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing_other_'.$this->otherListing->getId());

        $lockItem->create();
        $lockItem->makeShutdownFunction();
    }

    protected function eventAfterProcess()
    {
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing_other_'.$this->otherListing->getId());

        $lockItem->remove();
    }

    //########################################

    abstract protected function getLogsAction();

    abstract protected function getActionType();

    //########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    //########################################

    protected function logRequestMessages()
    {
        foreach ($this->getRequestObject()->getWarningMessages() as $message) {

            $message = array(
                parent::MESSAGE_TEXT_KEY => $message,
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_WARNING
            );

            $this->getLogger()->logListingOtherMessage($this->otherListing, $message,
                                                       Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_Logger
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getLogger()
    {
        if (is_null($this->logger)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Logger $logger */
            $logger = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Logger');

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

    private function getOrmActionType()
    {
        switch ($this->getActionType()) {
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

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Request
     */
    protected function getRequestObject()
    {
        if (is_null($this->requestObject)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Request $request */
            $request = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Type_'.$this->getOrmActionType().'_Request');

            $request->setParams($this->params);
            $request->setListingOther($this->otherListing);
            $request->setConfigurator($this->otherListing->getActionConfigurator());

            $this->requestObject = $request;
        }
        return $this->requestObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Response
     */
    protected function getResponseObject()
    {
        if (is_null($this->responseObject)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Response $response */
            $response = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_Type_'.$this->getOrmActionType().'_Response');

            $response->setParams($this->params);
            $response->setListingOther($this->otherListing);
            $response->setConfigurator($this->otherListing->getActionConfigurator());
            $response->setRequestData($this->getRequestDataObject());

            $this->responseObject = $response;
        }
        return $this->responseObject;
    }

    // ---------------------------------------

    /**
     * @param array $data
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_RequestData
     */
    protected function buildRequestDataObject(array $data)
    {
        if (is_null($this->requestDataObject)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Other_Action_RequestData $requestData */
            $requestData = Mage::getModel('M2ePro/Ebay_Listing_Other_Action_RequestData');

            $requestData->setData($data);
            $requestData->setListingOther($this->otherListing);

            $this->requestDataObject = $requestData;
        }
        return $this->requestDataObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Action_RequestData
     */
    protected function getRequestDataObject()
    {
        return $this->requestDataObject;
    }

    //########################################
}