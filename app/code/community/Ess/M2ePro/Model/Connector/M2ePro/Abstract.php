<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_M2ePro_Abstract extends Ess_M2ePro_Model_Connector_Command
{
    const COMPONENT = 'M2ePro';
    const COMPONENT_VERSION = 4;

    // ########################################

    protected function getComponent()
    {
        return self::COMPONENT;
    }

    protected function getComponentVersion()
    {
        return self::COMPONENT_VERSION;
    }

    // ########################################
}