<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_AutoAction_Mode_Category_Group_Grid
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Category_Group_Grid
{
    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_common_listing_autoAction/getCategoryGroupGrid', array('_current' => true));
    }

    //########################################
}