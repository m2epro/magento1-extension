<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Processing_Request extends Ess_M2ePro_Model_Abstract
{
    const PERFORM_TYPE_SINGLE  = 1;
    const PERFORM_TYPE_PARTIAL = 2;

    const STATUS_NOT_FOUND  = 'not_found';
    const STATUS_COMPLETE   = 'completed';
    const STATUS_PROCESSING = 'processing';

    const MAX_LIFE_TIME_INTERVAL = 86400; // 1 day

    /** @var Ess_M2ePro_Model_Connector_ResponserRunner $responserRunner */
    private $responserRunner = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Processing_Request');
    }

    //########################################

    public function getComponent()
    {
        return $this->getData('component');
    }

    /**
     * @return int
     */
    public function getPerformType()
    {
        return (int)$this->getData('perform_type');
    }

    public function getNextPart()
    {
        return $this->getData('next_part');
    }

    // ---------------------------------------

    public function getHash()
    {
        return $this->getData('hash');
    }

    public function getProcessingHash()
    {
        return $this->getData('processing_hash');
    }

    // ---------------------------------------

    public function getRequestBody()
    {
        return $this->getData('request_body');
    }

    public function getResponserModel()
    {
        return $this->getData('responser_model');
    }

    public function getResponserParams()
    {
        return $this->getData('responser_params');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isPerformTypeSingle()
    {
        return $this->getPerformType() == self::PERFORM_TYPE_SINGLE;
    }

    /**
     * @return bool
     */
    public function isPerformTypePartial()
    {
        return $this->getPerformType() == self::PERFORM_TYPE_PARTIAL;
    }

    //########################################

    /**
     * @return array
     */
    public function getDecodedRequestBody()
    {
        return @json_decode($this->getRequestBody(),true);
    }

    /**
     * @return array
     */
    public function getDecodedResponserParams()
    {
        return @json_decode($this->getResponserParams(),true);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Connector_ResponserRunner
     */
    public function getResponserRunner()
    {
        if (!is_null($this->responserRunner)) {
            return $this->responserRunner;
        }

        $this->responserRunner = Mage::getModel('M2ePro/Connector_ResponserRunner');
        $this->responserRunner->setProcessingRequest($this);

        return $this->responserRunner;
    }

    //########################################
}