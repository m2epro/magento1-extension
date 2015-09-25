<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Buy_Account_Add_EntityRequester
    extends Ess_M2ePro_Model_Connector_Buy_Requester
{
    // ########################################

    protected function getCommand()
    {
        return array('account','add','entity');
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
        return array(
            'title' => $this->account->getTitle(),
            'web_login' => $this->params['web_login'],
            'web_password' => $this->params['web_password'],
            'ftp_login' => $this->params['ftp_login'],
            'ftp_password' => $this->params['ftp_password'],
            'ftp_inventory_access' => $this->params['ftp_inventory_access'],
            'ftp_orders_access' => $this->params['ftp_orders_access'],
            'ftp_new_sku_access' => $this->params['ftp_new_sku_access']
        );
    }

    // ########################################
}