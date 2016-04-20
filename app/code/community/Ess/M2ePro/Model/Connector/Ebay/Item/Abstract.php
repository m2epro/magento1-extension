<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Ebay_Item_Abstract
    extends Ess_M2ePro_Model_Connector_Ebay_Abstract
{
    const TIMEOUT_INCREMENT_FOR_ONE_IMAGE = 30; // seconds

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger
     */
    protected $logger = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
     */
    protected $configurator = NULL;

    //########################################

    public function process()
    {
        try {

            $this->getLogger()->setStatus(
                Ess_M2ePro_Helper_Data::STATUS_SUCCESS
            );

            if (!$this->isNeedSendRequest()) {
                return array();
            }

            $this->eventBeforeProcess();

            $result = parent::process();

        } catch (Exception $exception) {
            $this->eventAfterProcess();
            throw $exception;
        }

        $this->eventAfterProcess();

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

    public function getLogsActionId()
    {
        return $this->params['logs_action_id'];
    }

    // ---------------------------------------

    protected function eventBeforeProcess() {}

    protected function eventAfterProcess() {}

    //########################################

    abstract protected function getLogsAction();

    abstract protected function getActionType();

    // ---------------------------------------

    abstract protected function isNeedSendRequest();

    //########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function isImagesUploadFailed($messages)
    {
        foreach ($messages as $message) {
            if ((int)$message[parent::MESSAGE_CODE_KEY] == 32704531) {
                return true;
            }
        }
        return false;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Logger
     * @throws Ess_M2ePro_Model_Exception
     */
    protected function getLogger()
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

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        if (is_null($this->configurator)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $configurator */

            $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
            $configurator->setParams($this->params);

            $this->configurator = $configurator;
        }

        return $this->configurator;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
     */
    protected function makeRequestObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request $request */

        $request = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Type_'.$this->getOrmActionType().'_Request');

        $request->setParams($this->params);
        $request->setListingProduct($listingProduct);
        $request->setConfigurator($listingProduct->getActionConfigurator());

        return $request;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @param Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData $requestData
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
     */
    protected function makeResponseObject(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                          Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData $requestData)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response $response */

        $response = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Type_'.$this->getOrmActionType().'_Response');

        $response->setParams($this->params);
        $response->setListingProduct($listingProduct);
        $response->setConfigurator($listingProduct->getActionConfigurator());
        $response->setRequestData($requestData);

        return $response;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @param array $data
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function makeRequestDataObject(Ess_M2ePro_Model_Listing_Product $listingProduct, array $data)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData $requestData */

        $requestData = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_RequestData');

        $requestData->setData($data);
        $requestData->setListingProduct($listingProduct);

        return $requestData;
    }

    //########################################

    private function getOrmActionType()
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