<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_ProductType_Help
    extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/amazon/productType/help.phtml');
    }
}