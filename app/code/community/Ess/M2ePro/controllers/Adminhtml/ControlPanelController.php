<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_ControlPanelController
    extends Ess_M2ePro_Controller_Adminhtml_ControlPanel_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->getLayout()
            ->getBlock('head')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/ControlPanel/Inspection.js')
            ->addCss('M2ePro/css/Plugin/DropDown.css');

        $this->_initPopUp();

        return $this;
    }

    /**
     * @title "First Test"
     * @description "Command for quick development"
     */
    public function firstTestAction()
    {
        return null;
    }

    /**
     * @title "Second Test"
     * @description "Command for quick development"
     */
    public function secondTestAction()
    {
        return null;
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_controlPanel'))
            ->renderLayout();
    }

    //########################################

    public function summaryTabAction()
    {
        $blockHtml = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_controlPanel_tabs_summary')
            ->toHtml();

        $this->getResponse()->setBody($blockHtml);
    }

    public function databaseTabAction()
    {
        $blockHtml = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_controlPanel_tabs_database')
            ->toHtml();

        $this->getResponse()->setBody($blockHtml);
    }

    public function inspectionTabAction()
    {
        $blockHtml = $this->loadLayout()
            ->getLayout()
            ->createBlock('M2ePro/adminhtml_controlPanel_tabs_inspection')
            ->toHtml();

        $this->getResponse()->setBody($blockHtml);

    }

    //########################################
}
