<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_M2ePro_Connector_Server_Servicing_UpdateData
    extends Ess_M2ePro_Model_Connector_Command_RealTime
{
    //########################################

    protected function getCommand()
    {
        return array('servicing', 'update', 'data');
    }

    public function getRequestData()
    {
        return $this->_params;
    }

    //########################################

    protected function buildConnectionInstance()
    {
        $connection = parent::buildConnectionInstance();
        $connection->setCanIgnoreMaintenance(true);

        return $connection;
    }

    //########################################
}
