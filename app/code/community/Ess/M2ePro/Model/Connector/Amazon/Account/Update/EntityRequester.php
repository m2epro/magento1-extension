<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Amazon_Account_Update_EntityRequester
    extends Ess_M2ePro_Model_Connector_Amazon_Requester
{
    // ########################################

    protected function getCommand()
    {
        return array('account','update','entity');
    }

    // ########################################

    protected function getResponserParams()
    {
        return array(
            'account_id' => $this->account->getId()
        );
    }

    // ########################################

    public function setProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::setProcessingLocks($processingRequest);

        $this->account->addObjectLock(NULL, $processingRequest->getHash());
        $this->account->addObjectLock('server_synchronize', $processingRequest->getHash());
        $this->account->addObjectLock('adding_to_server', $processingRequest->getHash());
    }

    // ########################################

    protected function getRequestData()
    {
        return $this->params;
    }

    // ########################################
}