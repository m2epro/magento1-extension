<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Command_Pending_Processing_Partial_Runner
    extends Ess_M2ePro_Model_Connector_Command_Pending_Processing_Runner
{
    /** @var Ess_M2ePro_Model_Request_Pending_Partial $requestPendingPartial */
    private $requestPendingPartial = NULL;

    // ##################################

    public function getType()
    {
        return Ess_M2ePro_Model_Processing::TYPE_PARTIAL;
    }

    // ##################################

    protected function getResponse()
    {
        if (!is_null($this->response)) {
            return $this->response;
        }

        $this->response = Mage::getModel('M2ePro/Connector_Connection_Response');

        $params = $this->getParams();
        if (!empty($params['request_time'])) {
            $this->response->setRequestTime($params['request_time']);
        }

        return $this->response;
    }

    // ##################################

    public function processSuccess()
    {
        try {
            $data = $this->getNextData();

            if (empty($data)) {
                if ($this->getMessages()) {
                    $this->getResponse()->initFromPreparedResponse(array(), $this->getMessages());
                    $this->getResponser(true)->process();
                }

                return true;
            }

            $this->incrementNextDataPartNumber();

            $this->getResponse()->initFromPreparedResponse($data);
            $this->getResponser(true)->process();
        } catch (Exception $exception) {
            $this->getResponser()->failDetected($exception->getMessage());
            return true;
        }

        return false;
    }

    public function processExpired()
    {
        $this->getResponser()->failDetected($this->getExpiredErrorMessage());
    }

    public function complete()
    {
        try {
            parent::complete();
        } catch (Exception $exception) {
            $this->getResponser()->failDetected($exception->getMessage());
            throw $exception;
        }
    }

    // ##################################

    protected function eventBefore()
    {
        parent::eventBefore();

        $params = $this->getParams();

        $requestPendingPartialCollection = Mage::getResourceModel('M2ePro/Request_Pending_Partial_Collection');
        $requestPendingPartialCollection->addFieldToFilter('component', $params['component']);
        $requestPendingPartialCollection->addFieldToFilter('server_hash', $params['server_hash']);

        /** @var Ess_M2ePro_Model_Request_Pending_Partial $requestPendingPartial */
        $requestPendingPartial = $requestPendingPartialCollection->getFirstItem();

        if (!$requestPendingPartial->getId()) {
            $requestPendingPartial->setData(array(
                'component'       => $params['component'],
                'server_hash'     => $params['server_hash'],
                'next_part'       => 1,
                'expiration_date' => Mage::helper('M2ePro')->getDate(
                    Mage::helper('M2ePro')->getCurrentGmtDate(true)+self::PENDING_REQUEST_MAX_LIFE_TIME
                )
            ));

            $requestPendingPartial->save();
        }

        $requesterPartial = Mage::getModel('M2ePro/Connector_Command_Pending_Processing_Partial');
        $requesterPartial->setData(array(
            'processing_id'              => $this->getProcessingObject()->getId(),
            'request_pending_partial_id' => $requestPendingPartial->getId(),
            'next_data_part_number'      => 1,
        ));

        $requesterPartial->save();
    }

    // ##################################

    private function getNextData()
    {
        if (is_null($this->getRequestPendingPartialObject())) {
            return array();
        }

        return $this->getRequestPendingPartialObject()->getResultData($this->getNextDataPartNumber());
    }

    private function getMessages()
    {
        if (is_null($this->getRequestPendingPartialObject())) {
            return array();
        }

        return $this->getRequestPendingPartialObject()->getResultMessages();
    }

    // ##################################

    protected function getRequestPendingPartialObject()
    {
        if (!is_null($this->requestPendingPartial)) {
            return $this->requestPendingPartial;
        }

        $resultData = $this->getProcessingObject()->getResultData();
        if (empty($resultData['request_pending_partial_id'])) {
            return NULL;
        }

        $requestPendingPartialId = (int)$resultData['request_pending_partial_id'];

        $requestPendingPartial = Mage::getModel('M2ePro/Request_Pending_Partial')->load($requestPendingPartialId);
        if (!$requestPendingPartial->getId()) {
            return NULL;
        }

        return $this->requestPendingPartial = $requestPendingPartial;
    }

    // ##################################

    protected function getNextDataPartNumber()
    {
        $resultData = $this->getProcessingObject()->getResultData();
        if (empty($resultData['next_data_part_number'])) {
            return 1;
        }

        return (int)$resultData['next_data_part_number'];
    }

    protected function incrementNextDataPartNumber()
    {
        $resultData = $this->getProcessingObject()->getResultData();
        $resultData['next_data_part_number'] = $this->getNextDataPartNumber() + 1;
        $this->getProcessingObject()->setSettings('result_data', $resultData);
        $this->getProcessingObject()->save();
    }

    // ##################################
}