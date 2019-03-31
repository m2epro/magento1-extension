<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Request_Pending_Partial extends Ess_M2ePro_Model_Abstract
{
    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Request_Pending_Partial');
    }

    //####################################

    public function getComponent()
    {
        return $this->getData('component');
    }

    //------------------------------------

    public function getServerHash()
    {
        return $this->getData('server_hash');
    }

    //------------------------------------

    public function getNextPart()
    {
        return $this->getData('next_part');
    }

    //------------------------------------

    public function getResultMessages()
    {
        return $this->getSettings('result_messages');
    }

    //------------------------------------

    public function getExpirationDate()
    {
        return $this->getData('expiration_date');
    }

    public function isCompleted()
    {
        return (bool)$this->getData('is_completed');
    }

    //####################################

    public function getResultData($partNumber)
    {
        return $this->getResource()->getResultData($this, (int)$partNumber);
    }

    public function addResultData(array $data)
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Instance must be loaded first.');
        }

        $this->getResource()->addResultData($this, $this->getNextPart(), $data);
        $this->setData('next_part', $this->getNextPart() + 1);
        $this->save();
    }

    //####################################

    public function deleteInstance()
    {
        if (!parent::deleteInstance()) {
            return false;
        }

        $this->getResource()->deleteResultData($this);

        return true;
    }

    //####################################
}