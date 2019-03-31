<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Request_Pending_Single extends Ess_M2ePro_Model_Abstract
{
    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Request_Pending_Single');
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

    public function getResultData()
    {
        return $this->getSettings('result_data');
    }

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
}