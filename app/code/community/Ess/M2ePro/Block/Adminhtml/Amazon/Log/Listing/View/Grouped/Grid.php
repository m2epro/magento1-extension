<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Log_Listing_View_Grouped_Grid
    extends Ess_M2ePro_Block_Adminhtml_Log_Listing_View_Grouped_AbstractGrid
{

    //########################################

    protected function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Amazon::NICK;
    }

    //########################################

    protected function getExcludedActionTitles()
    {
        return array();
    }

    //########################################
}
