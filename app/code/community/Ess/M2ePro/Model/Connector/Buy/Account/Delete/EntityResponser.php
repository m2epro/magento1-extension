<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Buy_Account_Delete_EntityResponser
    extends Ess_M2ePro_Model_Connector_Buy_Responser
{
    // ########################################

    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        try {
            $this->getAccount()->deleteObjectLocks(NULL, $processingRequest->getHash());
            $this->getAccount()->deleteObjectLocks('server_synchronize', $processingRequest->getHash());
            $this->getAccount()->deleteObjectLocks('deleting_from_server', $processingRequest->getHash());
        } catch(Exception $exception) {}
    }

    // ########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function processResponseData($response)
    {
        return $response;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    // ########################################
}