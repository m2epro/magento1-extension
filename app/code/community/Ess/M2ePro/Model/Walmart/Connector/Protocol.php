<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Connector_Protocol extends Ess_M2ePro_Model_Connector_Protocol
{
    public function getComponent()
    {
        return 'Walmart';
    }

    public function getComponentVersion()
    {
        return 6;
    }
}