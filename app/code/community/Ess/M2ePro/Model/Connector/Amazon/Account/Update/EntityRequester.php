<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Amazon_Account_Update_EntityRequester
    extends Ess_M2ePro_Model_Connector_Amazon_Requester
{
    //########################################

    protected function getCommand()
    {
        return array('account','update','entity');
    }

    //########################################

    protected function getResponserParams()
    {
        return array(
            'account_id' => $this->account->getId()
        );
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function setProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::setProcessingLocks($processingRequest);

        $this->account->addObjectLock(NULL, $processingRequest->getHash());
        $this->account->addObjectLock('server_synchronize', $processingRequest->getHash());
        $this->account->addObjectLock('adding_to_server', $processingRequest->getHash());
    }

    //########################################

    protected function getRequestData()
    {
        return $this->params;
    }

    //########################################
}