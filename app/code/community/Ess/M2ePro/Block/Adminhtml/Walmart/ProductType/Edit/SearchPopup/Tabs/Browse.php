<?php

class Ess_M2ePro_Block_Adminhtml_Walmart_ProductType_Edit_SearchPopup_Tabs_Browse
    extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartProductTypeSearchPopupBrowse');
        $this->setTemplate('M2ePro/walmart/productType/edit/searchPopup/tabs/browse.phtml');
    }
}