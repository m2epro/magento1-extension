<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Description_Edit_Tabs_Specifics
    extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonTemplateDescriptionEditTabsSpecifics');
        //------------------------------

        $this->setTemplate('M2ePro/common/amazon/template/description/tabs/specifics.phtml');
    }

    // ####################################

    protected function _beforeToHtml()
    {
        //--
        $this->setData('attributes', Mage::helper('M2ePro/Magento_Attribute')->getAll());
        //--

        return parent::_beforeToHtml();
    }

    // ####################################
}