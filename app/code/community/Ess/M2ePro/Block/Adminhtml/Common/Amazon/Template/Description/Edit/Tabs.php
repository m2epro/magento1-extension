<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Description_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonTemplateDescriptionEditTabs');
        //------------------------------

        $this->setTitle(Mage::helper('M2ePro')->__('Configuration'));
        $this->setDestElementId('edit_form');
    }

    // ####################################

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label'   => Mage::helper('M2ePro')->__('General'),
            'title'   => Mage::helper('M2ePro')->__('General'),
            'content' => $this->getLayout()->createBlock(
                'M2ePro/adminhtml_common_amazon_template_description_edit_tabs_general'
            )->toHtml(),
        ));

        $this->addTab('definition', array(
            'label'   => Mage::helper('M2ePro')->__('Definition'),
            'title'   => Mage::helper('M2ePro')->__('Definition'),
            'content' => $this->getLayout()->createBlock(
                'M2ePro/adminhtml_common_amazon_template_description_edit_tabs_definition'
            )->toHtml(),
        ));

        $this->addTab('specifics', array(
            'label'   => Mage::helper('M2ePro')->__('Specifics'),
            'title'   => Mage::helper('M2ePro')->__('Specifics'),
            'content' => $this->getLayout()->createBlock(
                'M2ePro/adminhtml_common_amazon_template_description_edit_tabs_specifics'
            )->toHtml(),
        ));

        $this->setActiveTab($this->getRequest()->getParam('tab', 'general'));

        return parent::_beforeToHtml();
    }

    // ####################################
}