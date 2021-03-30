<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Other_Product_Grid
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Manually_Grid
{
    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_ebay_listing_categorySettings/otherCategoriesGrid',
            array(
                '_current' => true
            )
        );
    }

    //########################################
}
