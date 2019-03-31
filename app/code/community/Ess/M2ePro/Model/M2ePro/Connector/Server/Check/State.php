<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_M2ePro_Connector_Server_Check_State
    extends Ess_M2ePro_Model_Connector_Command_RealTime
{
    // ########################################

    protected function getCommand()
    {
        return array('server', 'check', 'state');
    }

    public function getRequestData()
    {
        return array();
    }

    protected function validateResponse()
    {
        return true;
    }

    // ########################################

    protected function buildConnectionInstance()
    {
        $connection = parent::buildConnectionInstance();
        $connection->setTimeout(30)
                   ->setServerBaseUrl($this->params['base_url'])
                   ->setServerHostName($this->params['hostname'])
                   ->setTryToSwitchEndpointOnError(false)
                   ->setTryToResendOnError(false);

        return $connection;
    }

    // ########################################
}