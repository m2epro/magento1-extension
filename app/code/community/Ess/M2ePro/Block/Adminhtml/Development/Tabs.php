<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    // ########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('developmentTabs');
        $this->setDestElementId('development_tab_container');
    }

    // ########################################

    protected function _prepareLayout()
    {
        $activeTab = $this->getRequest()->getParam('tab');
        $allowedTabs = array(
            Ess_M2ePro_Helper_View_Development::TAB_SUMMARY,
            Ess_M2ePro_Helper_View_Development::TAB_ABOUT,
            Ess_M2ePro_Helper_View_Development::TAB_INSPECTION,
            Ess_M2ePro_Helper_View_Development::TAB_DATABASE,
            Ess_M2ePro_Helper_View_Development::TAB_TOOLS,
            Ess_M2ePro_Helper_View_Development::TAB_MODULE,
            Ess_M2ePro_Helper_View_Development::TAB_DEBUG,
            Ess_M2ePro_Helper_View_Development::TAB_BUILD
        );

        //------------------------------
        $params = array('label' => Mage::helper('M2ePro')->__('Summary'));
        if ($activeTab == Ess_M2ePro_Helper_View_Development::TAB_SUMMARY || !in_array($activeTab,$allowedTabs)) {
            $params['content'] = $this->getLayout()
                                      ->createBlock('M2ePro/adminhtml_development_tabs_summary')->toHtml();
        } else {
            $params['url'] = $this->getUrl('*/adminhtml_development/summaryTab');
            $params['class'] = 'ajax';
        }
        $this->addTab(Ess_M2ePro_Helper_View_Development::TAB_SUMMARY, $params);
        //------------------------------

        //------------------------------
        $params = array('label' => Mage::helper('M2ePro')->__('About'));
        if ($activeTab == Ess_M2ePro_Helper_View_Development::TAB_ABOUT) {
            $params['content'] = $this->getLayout()
                                      ->createBlock('M2ePro/adminhtml_development_tabs_about')->toHtml();
        } else {
            $params['url'] = $this->getUrl('*/adminhtml_development/aboutTab');
            $params['class'] = 'ajax';
        }
        $this->addTab(Ess_M2ePro_Helper_View_Development::TAB_ABOUT, $params);
        //------------------------------

        $this->addTab(Ess_M2ePro_Helper_View_Development::TAB_INSPECTION, array(
            'label'     => Mage::helper('M2ePro')->__('Inspection'),
            'content'   => $this->getLayout()->createBlock('M2ePro/adminhtml_development_tabs_inspection')->toHtml(),
        ));

        //------------------------------
        $params = array('label' => Mage::helper('M2ePro')->__('Database'));
        if ($activeTab == Ess_M2ePro_Helper_View_Development::TAB_DATABASE) {
            $params['content'] = $this->getLayout()
                                      ->createBlock('M2ePro/adminhtml_development_tabs_database')->toHtml();
        } else {
            $params['url'] = $this->getUrl('*/adminhtml_development/databaseTab');
            $params['class'] = 'ajax';
        }
        $this->addTab(Ess_M2ePro_Helper_View_Development::TAB_DATABASE, $params);
        //------------------------------

        $this->addTab(Ess_M2ePro_Helper_View_Development::TAB_TOOLS, array(
            'label'     => Mage::helper('M2ePro')->__('Tools'),
            'content'   => $this->getLayout()->createBlock('M2ePro/adminhtml_development_tabs_tools')->toHtml(),
        ));
        $this->addTab(Ess_M2ePro_Helper_View_Development::TAB_MODULE, array(
            'label'     => Mage::helper('M2ePro')->__('Module'),
            'content'   => $this->getLayout()->createBlock('M2ePro/adminhtml_development_tabs_module')->toHtml(),
        ));
        $this->addTab(Ess_M2ePro_Helper_View_Development::TAB_DEBUG, array(
            'label'     => Mage::helper('M2ePro')->__('Debug'),
            'content'   => $this->getLayout()->createBlock('M2ePro/adminhtml_development_tabs_debug')->toHtml(),
        ));

        $block = 'M2ePro/adminhtml_development_tabs_build';
        $blockClassName = Mage::getConfig()->getBlockClassName($block);

        if (class_exists($blockClassName, false) || @mageFindClassFile($blockClassName)) {
            $this->addTab(Ess_M2ePro_Helper_View_Development::TAB_BUILD, array(
                'label'     => Mage::helper('M2ePro')->__('Build'),
                'content'   => $this->getLayout()->createBlock($block)->toHtml(),
            ));
        }

        $this->setActiveTab($activeTab);

        return parent::_prepareLayout();
    }

    // ########################################
}