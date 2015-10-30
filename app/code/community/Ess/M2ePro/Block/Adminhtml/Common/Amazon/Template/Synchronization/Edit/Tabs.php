<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Synchronization_Edit_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonTemplateSynchronizationEditTabs');
        // ---------------------------------------

        $this->setTitle(Mage::helper('M2ePro')->__('Configuration'));
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('list',array(
            'label'   => Mage::helper('M2ePro')->__('List Rules'),
            'title'   => Mage::helper('M2ePro')->__('List Rules'),
            'content' => $this->getLayout()
                               ->createBlock('M2ePro/adminhtml_common_amazon_template_synchronization_edit_tabs_list')
                               ->toHtml()
        ));

        $this->addTab('revise',array(
            'label'   => Mage::helper('M2ePro')->__('Revise Rules'),
            'title'   => Mage::helper('M2ePro')->__('Revise Rules'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_common_amazon_template_synchronization_edit_tabs_revise')
                              ->toHtml()
        ));

        $this->addTab('relist',array(
            'label'   => Mage::helper('M2ePro')->__('Relist Rules'),
            'title'   => Mage::helper('M2ePro')->__('Relist Rules'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_common_amazon_template_synchronization_edit_tabs_relist')
                              ->toHtml()
        ));

        $this->addTab('stop',array(
            'label'   => Mage::helper('M2ePro')->__('Stop Rules'),
            'title'   => Mage::helper('M2ePro')->__('Stop Rules'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_common_amazon_template_synchronization_edit_tabs_stop')
                              ->toHtml()
        ));

        $this->setActiveTab($this->getRequest()->getParam('tab', 'list'));

        return parent::_beforeToHtml();
    }

    //########################################
}