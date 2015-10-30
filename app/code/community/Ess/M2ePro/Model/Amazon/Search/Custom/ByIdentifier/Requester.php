<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Search_Custom_ByIdentifier_Requester
    extends Ess_M2ePro_Model_Connector_Amazon_Search_ByIdentifier_ItemsRequester
{
    //########################################

    protected function getQuery()
    {
        return $this->params['query'];
    }

    protected function getQueryType()
    {
        return $this->params['query_type'];
    }

    protected function getVariationBadParentModifyChildToSimple()
    {
        return $this->params['variation_bad_parent_modify_child_to_simple'];
    }

    //########################################

    protected function getRequestData()
    {
        return array_merge(
            parent::getRequestData(),
            array('only_realtime' => true)
        );
    }

    //########################################
}