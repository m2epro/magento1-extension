<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Exception_Connection extends Ess_M2ePro_Model_Exception
{
    // ########################################

    public function __construct($message, $additionalData = array())
    {
        parent::__construct($message, $additionalData, 0, false);
    }

    // ########################################
}