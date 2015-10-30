<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Requester extends Ess_M2ePro_Model_Connector_Protocol
{
    protected $params = array();

    //########################################

    public function __construct(array $params = array())
    {
        $this->params = $params;
    }

    //########################################

    public function process()
    {
        $responseData = $this->sendRequest();

        if (!is_array($responseData)) {
            $responseData = array($responseData);
        }

        /** @var Ess_M2ePro_Model_Connector_ResponserRunner $responserRunner */

        if (isset($responseData['processing_id'])) {
            /** @var $processingRequest Ess_M2ePro_Model_Processing_Request */
            $processingRequest = $this->createProcessingRequest((string)$responseData['processing_id']);
            $responserRunner = $processingRequest->getResponserRunner();
        } else {
            $responserRunner = Mage::getModel('M2ePro/Connector_ResponserRunner');
            $responserRunner->setResponserModelName($this->getResponserModelName());
            $responserRunner->setResponserParams($this->getResponserParams());
        }

        $responserRunner->start($this);

        if (isset($responseData['processing_id'])) {
            return null;
        }

        if (!$responserRunner->process($responseData, $this->messages)) {
            return false;
        }

        $responserRunner->complete();

        return $responserRunner->getParsedResponseData();
    }

    //########################################

    public function eventBeforeExecuting() {}

    // ---------------------------------------

    public function eventBeforeProcessing() {}

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     */
    public function setProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest) {}

    //########################################

    /**
     * @param $processingId
     * @return Ess_M2ePro_Model_Processing_Request
     * @throws Exception
     */
    protected function createProcessingRequest($processingId)
    {
        $processingRequestData = array_merge(
            $this->getProcessingData(),
            array(
                'processing_hash' => $processingId,
                'hash' => Mage::helper('M2ePro')->generateUniqueHash(Mage::helper('M2ePro/Client')->getDomain()),
            )
        );

        if ($processingRequestData['perform_type'] == Ess_M2ePro_Model_Processing_Request::PERFORM_TYPE_PARTIAL) {
            $processingRequestData['next_part'] = 1;
        }

        /** @var Ess_M2ePro_Model_Processing_Request $processingRequest */
        $processingRequest = Mage::getModel('M2ePro/Processing_Request');
        $processingRequest->setData($processingRequestData);
        $processingRequest->save();

        return $processingRequest;
    }

    /**
     * @return array
     */
    protected function getProcessingData()
    {
        $expirationDate = Mage::helper('M2ePro')->getDate(
            Mage::helper('M2ePro')->getCurrentGmtDate(true)+Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL
        );

        return array(
            'component'        => strtolower($this->getComponent()),
            'perform_type'     => Ess_M2ePro_Model_Processing_Request::PERFORM_TYPE_SINGLE,
            'request_body'     => json_encode($this->request),
            'responser_model'  => $this->getResponserModelName(),
            'responser_params' => json_encode((array)$this->getResponserParams()),
            'expiration_date'  => $expirationDate,
        );
    }

    //########################################

    protected function getResponserModelName()
    {
        $responserClassName = preg_replace('/Requester$/', '', get_class($this)).'Responser';
        $responserModelName = preg_replace('/^Ess_M2ePro_Model_/', 'M2ePro/', $responserClassName);

        return $responserModelName;
    }

    /**
     * @return array
     */
    protected function getResponserParams()
    {
        return $this->params;
    }

    //########################################
}