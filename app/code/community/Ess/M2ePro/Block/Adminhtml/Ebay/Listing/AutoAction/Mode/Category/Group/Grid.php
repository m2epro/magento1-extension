<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_AutoAction_Mode_Category_Group_Grid
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Category_Group_Grid
{
    // ########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing_autoAction/getCategoryGroupGrid', array('_current' => true));
    }

    // ########################################
}