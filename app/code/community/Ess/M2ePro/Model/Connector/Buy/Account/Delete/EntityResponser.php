<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Buy_Account_Delete_EntityResponser
    extends Ess_M2ePro_Model_Connector_Buy_Responser
{
    //########################################

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     */
    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        try {
            $this->getAccount()->deleteObjectLocks(NULL, $processingRequest->getHash());
            $this->getAccount()->deleteObjectLocks('server_synchronize', $processingRequest->getHash());
            $this->getAccount()->deleteObjectLocks('deleting_from_server', $processingRequest->getHash());
        } catch(Exception $exception) {}
    }

    //########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function processResponseData($response)
    {
        return $response;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    //########################################
}