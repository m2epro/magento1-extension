<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_Edit_Primary_Tabs
    extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayConfigurationCategoryEditPrimaryTabs');
        // ---------------------------------------

        $this->setTitle(Mage::helper('M2ePro')->__('Category'));
        $this->setDestElementId('tabs_container');

        $this->setTemplate('widget/tabshoriz.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('chooser', array(
            'label'   => Mage::helper('M2ePro')->__('General'),
            'title'   => Mage::helper('M2ePro')->__('General'),
            'content' => $this->getLayout()
                ->createBlock('M2ePro/adminhtml_ebay_configuration_category_edit_primary_tabs_chooser')
                ->toHtml(),
        ));

        $this->addTab('specific', array(
            'label'   => Mage::helper('M2ePro')->__('Specifics'),
            'title'   => Mage::helper('M2ePro')->__('Specifics'),
            'content' => $this->getLayout()
                ->createBlock('M2ePro/adminhtml_ebay_configuration_category_edit_primary_tabs_specific')
                ->toHtml(),
        ));

        $this->setActiveTab($this->getRequest()->getParam('tab', 'chooser'));

        return parent::_beforeToHtml();
    }

    //########################################
}