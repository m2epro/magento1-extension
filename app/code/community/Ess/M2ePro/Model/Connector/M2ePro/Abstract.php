<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_M2ePro_Abstract extends Ess_M2ePro_Model_Connector_Command
{
    const COMPONENT = 'M2ePro';
    const COMPONENT_VERSION = 4;

    //########################################

    /**
     * @return string
     */
    protected function getComponent()
    {
        return self::COMPONENT;
    }

    /**
     * @return int
     */
    protected function getComponentVersion()
    {
        return self::COMPONENT_VERSION;
    }

    //########################################
}