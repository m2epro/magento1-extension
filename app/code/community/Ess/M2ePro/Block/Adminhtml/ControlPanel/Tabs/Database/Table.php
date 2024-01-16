<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Tabs_Database_Table extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelDatabaseTable');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_controlPanel_tabs_database_table';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $tableName = $this->getRequest()->getParam('table');
        $component = $this->getRequest()->getParam('component');
        $this->_headerText = Mage::helper('M2ePro')->__('Manage Table "%table_name%"', $tableName);

        if ($this->isMergeModeEnabled() && $component &&
            Mage::helper('M2ePro/Module_Database_Structure')->isTableHorizontalParent($tableName)) {
            $this->_headerText .= " <span style='color: grey; font-size: small;'>[merged {$component} data]</span>";
        }

        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $url = Mage::helper('M2ePro/View_ControlPanel')->getPageDatabaseTabUrl();
        $this->_addButton(
            'back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => "window.open('{$url}','_blank')",
            'class'     => 'back'
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton(
            'additional-actions', array(
            'label'     => Mage::helper('M2ePro')->__('Additional Actions'),
            'onclick'   => '',
            'class'     => 'button_link additional-actions-button-drop-down',
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton(
            'add_row', array(
            'label'     => Mage::helper('M2ePro')->__('Append Row'),
            'onclick'   => 'ControlPanelDatabaseGridObj.openTableCellsPopup(\'add\')',
            'class'     => 'success'
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $helper = Mage::helper('M2ePro/Module_Database_Structure');

        if ($helper->isTableHorizontalChild($tableName) ||
            ($helper->isTableHorizontalParent($tableName) && $this->isMergeModeEnabled() && $component)) {
            $labelAdd = $this->isMergeModeEnabled() ? 'disable' : 'enable';

            $this->_addButton(
                'merge_mode', array(
                'label'     => Mage::helper('M2ePro')->__("Join Full Collection [{$labelAdd}]"),
                'onclick'   => 'ControlPanelDatabaseGridObj.switchMergeMode()',
                'class'     => !$this->isMergeModeEnabled() ? 'success' : 'fail'
                )
            );
        }

        // ---------------------------------------
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Plugin/DropDown.js')
             ->addCss('M2ePro/css/Plugin/DropDown.css');

        return parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        return $this->getAdditionalActionsButtonHtml() . parent::_toHtml();
    }

    //########################################

    public function getAdditionalActionsButtonHtml()
    {
        $data = array(
            'target_css_class' => 'additional-actions-button-drop-down',
            'items'            => array(
                array(
                    'url'    => $this->getUrl('*/adminhtml_controlPanel_tools_magento/clearMagentoCache'),
                    'target' => '_blank',
                    'label'  => Mage::helper('M2ePro')->__('Flush Magento Cache')
                )
            )
        );
        $dropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
        $dropDownBlock->setData($data);

        return $dropDownBlock->toHtml();
    }

    public function isMergeModeEnabled()
    {
        $key = Ess_M2ePro_Block_Adminhtml_ControlPanel_Tabs_Database_Table_Grid::MERGE_MODE_COOKIE_KEY;
        return (bool)Mage::app()->getCookie()->get($key);
    }

    //########################################
}
