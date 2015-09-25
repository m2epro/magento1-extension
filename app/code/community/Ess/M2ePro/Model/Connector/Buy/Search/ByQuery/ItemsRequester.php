<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Buy_Search_ByQuery_ItemsRequester
    extends Ess_M2ePro_Model_Connector_Buy_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','search','byQuery');
    }

    // ########################################

    abstract protected function getQuery();

    // ########################################

    protected function getRequestData()
    {
        return array(
            'query' => $this->getQuery(),
        );
    }

    // ########################################
}