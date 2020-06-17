<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_ActiveRecord_Relation_Child_Ebay_EbayAbstract
    extends Ess_M2ePro_Model_ActiveRecord_Relation_ChildAbstract
{
    //########################################

    public function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Ebay::NICK;
    }

    //########################################
}
