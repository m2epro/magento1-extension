<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_AutoAction_Mode_Category
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_CategoryAbstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->createHelpBlock($this->getLink('M2ePro/adminhtml_walmart_listing_autoAction_mode'));
    }

    //########################################

    protected function prepareGroupsGrid()
    {
        $groupGrid = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_listing_autoAction_mode_category_group_grid'
        );
        $groupGrid->prepareGrid();
        $this->setChild('group_grid', $groupGrid);

        return $groupGrid;
    }

    //########################################
}
