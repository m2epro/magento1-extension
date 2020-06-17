<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Support_Tabs extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('support');
        $this->setDestElementId('support_tab_container');
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->addTab(
            'results', array(
            'label'     => Mage::helper('M2ePro')->__('Search Results'),
            'content'   => '',
            'active'    => !$this->getIsFromError(),
            )
        );

        $this->addTab(
            'support_form', array(
            'label'     => Mage::helper('M2ePro')->__('Contact Support'),
            'content'   => $this->getLayout()->createBlock('M2ePro/adminhtml_support_contactForm')->toHtml(),
            'active'    => $this->getIsFromError(),
            )
        );

        return parent::_prepareLayout();
    }

    //########################################
}
