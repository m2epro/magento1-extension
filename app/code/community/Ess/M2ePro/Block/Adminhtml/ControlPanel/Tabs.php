<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Tabs extends Ess_M2ePro_Block_Adminhtml_Widget_Tabs
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('widget/tabshoriz.phtml');
        $this->setId('controlPanelTabs');
        $this->setDestElementId('controlPanel_tab_container');
    }

    //########################################

    protected function _prepareLayout()
    {
        $activeTab = $this->getRequest()->getParam('tab');
        $allowedTabs = array(
            Ess_M2ePro_Helper_View_ControlPanel::TAB_SUMMARY,
            Ess_M2ePro_Helper_View_ControlPanel::TAB_INSPECTION,
            Ess_M2ePro_Helper_View_ControlPanel::TAB_DATABASE,
            Ess_M2ePro_Helper_View_ControlPanel::TAB_TOOLS,
            Ess_M2ePro_Helper_View_ControlPanel::TAB_MODULE,
            Ess_M2ePro_Helper_View_ControlPanel::TAB_CRON,
            Ess_M2ePro_Helper_View_ControlPanel::TAB_DEBUG
        );

        // ---------------------------------------
        $params = array('label' => Mage::helper('M2ePro')->__('Summary'));
        if ($activeTab == Ess_M2ePro_Helper_View_ControlPanel::TAB_SUMMARY || !in_array($activeTab, $allowedTabs)) {
            $params['content'] = $this->getLayout()
                                      ->createBlock('M2ePro/adminhtml_controlPanel_tabs_summary')->toHtml();
        } else {
            $params['url'] = $this->getUrl('*/adminhtml_controlPanel/summaryTab');
            $params['class'] = 'ajax';
        }

        $this->addTab(Ess_M2ePro_Helper_View_ControlPanel::TAB_SUMMARY, $params);
        // ---------------------------------------

        $this->addTab(
            Ess_M2ePro_Helper_View_ControlPanel::TAB_INSPECTION, array(
            'label'     => Mage::helper('M2ePro')->__('Inspection'),
            'content'   => $this->getLayout()->createBlock('M2ePro/adminhtml_controlPanel_tabs_inspection')->toHtml(),
            )
        );

        // ---------------------------------------
        $params = array('label' => Mage::helper('M2ePro')->__('Database'));
        if ($activeTab == Ess_M2ePro_Helper_View_ControlPanel::TAB_DATABASE) {
            $params['content'] = $this->getLayout()
                                      ->createBlock('M2ePro/adminhtml_controlPanel_tabs_database')->toHtml();
        } else {
            $params['url'] = $this->getUrl('*/adminhtml_controlPanel/databaseTab');
            $params['class'] = 'ajax';
        }

        $this->addTab(Ess_M2ePro_Helper_View_ControlPanel::TAB_DATABASE, $params);
        // ---------------------------------------

        $this->addTab(
            Ess_M2ePro_Helper_View_ControlPanel::TAB_TOOLS, array(
            'label'     => Mage::helper('M2ePro')->__('Tools'),
            'content'   => $this->getLayout()->createBlock('M2ePro/adminhtml_controlPanel_tabs_tools')->toHtml(),
            )
        );
        $this->addTab(
            Ess_M2ePro_Helper_View_ControlPanel::TAB_MODULE, array(
            'label'     => Mage::helper('M2ePro')->__('Module'),
            'content'   => $this->getLayout()->createBlock('M2ePro/adminhtml_controlPanel_tabs_module')->toHtml(),
            )
        );
        $this->addTab(
            Ess_M2ePro_Helper_View_ControlPanel::TAB_CRON, array(
            'label'     => Mage::helper('M2ePro')->__('Cron'),
            'content'   => $this->getLayout()->createBlock('M2ePro/adminhtml_controlPanel_tabs_cron')->toHtml(),
            )
        );
        $this->addTab(
            Ess_M2ePro_Helper_View_ControlPanel::TAB_DEBUG, array(
            'label'     => Mage::helper('M2ePro')->__('Debug'),
            'content'   => $this->getLayout()->createBlock('M2ePro/adminhtml_controlPanel_tabs_debug')->toHtml(),
            )
        );

        $this->setActiveTab($activeTab);

        return parent::_prepareLayout();
    }

    //########################################
}
