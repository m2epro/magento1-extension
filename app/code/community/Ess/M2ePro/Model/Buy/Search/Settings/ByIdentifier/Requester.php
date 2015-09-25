<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Search_Settings_ByIdentifier_Requester
    extends Ess_M2ePro_Model_Connector_Buy_Search_ByIdentifier_ItemsRequester
{
    // ########################################

    protected function getQuery()
    {
        return $this->params['query'];
    }

    protected function getSearchType()
    {
        return $this->params['search_type'];
    }

    // ########################################
}