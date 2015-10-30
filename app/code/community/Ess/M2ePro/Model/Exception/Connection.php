<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Exception_Connection extends Ess_M2ePro_Model_Exception
{
    //########################################

    public function __construct($message, $additionalData = array())
    {
        parent::__construct($message, $additionalData, 0, false);
    }

    //########################################
}