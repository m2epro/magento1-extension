<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Search_Custom_ByAsin_Requester
    extends Ess_M2ePro_Model_Amazon_Connector_Search_ByAsin_ItemsRequester
{
    //########################################

    protected function getQuery()
    {
        return $this->_params['query'];
    }

    protected function getVariationBadParentModifyChildToSimple()
    {
        return $this->_params['variation_bad_parent_modify_child_to_simple'];
    }

    //########################################

    public function getRequestData()
    {
        return array_merge(
            parent::getRequestData(),
            array('only_realtime' => true)
        );
    }

    //########################################
}