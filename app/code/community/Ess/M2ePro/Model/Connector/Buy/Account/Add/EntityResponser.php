<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Buy_Account_Add_EntityResponser
    extends Ess_M2ePro_Model_Connector_Buy_Responser
{
    // ########################################

    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        $this->getAccount()->deleteObjectLocks(NULL, $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('server_synchronize', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('adding_to_server', $processingRequest->getHash());
    }

    // ########################################

    protected function validateResponseData($response)
    {
        if (empty($response['hash']) || !isset($response['info'])) {
            return false;
        }

        return true;
    }

    protected function processResponseData($response)
    {
        /** @var $buyAccount Ess_M2ePro_Model_Buy_Account */
        $buyAccount = $this->getAccount()->getChildObject();

        $dataForUpdate = array(
            'server_hash' => $response['hash'],
            'info' => json_encode($response['info'])
        );

        $buyAccount->addData($dataForUpdate)->save();
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