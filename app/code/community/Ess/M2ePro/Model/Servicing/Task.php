<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Servicing_Task
{
    private $params = array();

    // ########################################

    /**
     * @return string
     */
    abstract public function getPublicNick();

    // ########################################

    public function getParams()
    {
        return $this->params;
    }

    public function setParams(array $params = array())
    {
        $this->params = $params;
    }

    // ########################################

    public function isAllowed()
    {
        return true;
    }

    // ----------------------------------------
    /**
     * @return array
     */
    abstract public function getRequestData();

    /**
     * @param array $data
     * @return null
     */
    abstract public function processResponseData(array $data);

    // ########################################
}