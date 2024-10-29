<?php

class Ess_M2ePro_Model_M2ePro_Connector_Protocol extends Ess_M2ePro_Model_Connector_Protocol
{
    public function getComponent()
    {
        return 'M2ePro';
    }

    public function getComponentVersion()
    {
        return 9;
    }
}