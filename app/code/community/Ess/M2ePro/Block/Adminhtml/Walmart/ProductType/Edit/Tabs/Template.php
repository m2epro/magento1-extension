<?php

class Ess_M2ePro_Block_Adminhtml_Walmart_ProductType_Edit_Tabs_Template
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function _construct()
    {
        parent::_construct();
        $this->setId('walmartProductTypeEditTabsTemplate');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
