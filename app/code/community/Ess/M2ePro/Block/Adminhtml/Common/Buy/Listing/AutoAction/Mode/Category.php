<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_AutoAction_Mode_Category
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Category
{
    //########################################

    protected function prepareGroupsGrid()
    {
        $groupGrid = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_common_buy_listing_autoAction_mode_category_group_grid');
        $groupGrid->prepareGrid();
        $this->setChild('group_grid', $groupGrid);

        return $groupGrid;
    }

    //########################################
}
