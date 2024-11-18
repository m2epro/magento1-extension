<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_ProductType_Edit_SearchPopup_Tabs_Search
    extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonProductTypeSearchPopupSearch');
        $this->setTemplate('M2ePro/amazon/productType/edit/searchPopup/tabs/search.phtml');
    }

}