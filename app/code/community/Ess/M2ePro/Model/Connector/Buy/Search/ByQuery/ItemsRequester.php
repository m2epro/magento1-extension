<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Buy_Search_ByQuery_ItemsRequester
    extends Ess_M2ePro_Model_Connector_Buy_Requester
{
    //########################################

    /**
     * @return array
     */
    public function getCommand()
    {
        return array('product','search','byQuery');
    }

    //########################################

    abstract protected function getQuery();

    //########################################

    /**
     * @return array
     */
    protected function getRequestData()
    {
        return array(
            'query' => $this->getQuery(),
        );
    }

    //########################################
}