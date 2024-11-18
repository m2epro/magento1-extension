<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_ProductType_Edit_SearchPopup_Tabs_Browse
    extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonProductTypeSearchPopupBrowse');
        $this->setTemplate('M2ePro/amazon/productType/edit/searchPopup/tabs/browse.phtml');
    }

}