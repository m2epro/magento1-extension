<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Search_Settings_ByQuery_Requester
    extends Ess_M2ePro_Model_Connector_Buy_Search_ByQuery_ItemsRequester
{
    //########################################

    protected function getQuery()
    {
        return $this->params['query'];
    }

    //########################################
}