<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_ControlPanel_Inspection_Result as Result;
use Ess_M2ePro_Model_ControlPanel_Inspection_Manager as Manager;

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    const NOT_SUCCESS_FILTER = 'not-success';

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('controlPanelInspectionsGrid');

        $this->setDefaultSort('state');
        $this->setDefaultDir('DESC');
        $this->setDefaultFilter(
            array(
                'state' => self::NOT_SUCCESS_FILTER
            )
        );
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = new Ess_M2ePro_Model_Collection_Custom();
        $manager = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Manager');

        foreach ($manager->getInspections() as $inspection) {
            /** @var Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection $inspection */
            $row = array(
                'id'              => $manager->getId($inspection),
                'title'           => $inspection->getTitle(),
                'description'     => $inspection->getDescription(),
                'execution_speed' => $inspection->getExecutionSpeed(),
                'state'           => (string)$inspection->getState(),
                'need_attention'  => (string)(int)($inspection->getState() > Result::STATE_SUCCESS),
                'inspection'      => $inspection
            );
            $collection->addItem(new Varien_Object($row));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title',
            array(
                'header'       => Mage::helper('M2ePro')->__('Title'),
                'align'        => 'left',
                'type'         => 'text',
                'width'        => '20%',
                'index'        => 'title',
                'filter_index' => 'title',
                'filter_condition_callback' => array($this, 'callbackFilterLike'),
                'frame_callback' => array($this, 'callbackColumnTitle')
            )
        );

        $this->addColumn(
            'details',
            array(
                'header'       => Mage::helper('M2ePro')->__('Details'),
                'align'        => 'left',
                'type'         => 'text',
                'width'        => '40%',
                'filter_index' => false,
                'frame_callback' => array($this, 'callbackColumnDetails')
            )
        );

        $this->addColumn(
            'state',
            array(
                'header'    => Mage::helper('M2ePro')->__('State'),
                'align'     => 'right',
                'width'     => '10%',
                'index'     => 'state',
                'type'      => 'options',
                'options'   => array(
                    self::NOT_SUCCESS_FILTER => Mage::helper('M2ePro')->__('Error | Warning | Notice'),
                    Result::STATE_ERROR      => Mage::helper('M2ePro')->__('Error'),
                    Result::STATE_WARNING    => Mage::helper('M2ePro')->__('Warning'),
                    Result::STATE_NOTICE     => Mage::helper('M2ePro')->__('Notice'),
                    Result::STATE_SUCCESS    => Mage::helper('M2ePro')->__('Success'),
                ),
                'filter_index' => 'state',
                'filter_condition_callback' => array($this, 'callbackFilterMatch'),
                'frame_callback' => array($this, 'callbackColumnState')
            )
        );

        $this->addColumn(
            'execution_speed',
            array(
                'header'       => Mage::helper('M2ePro')->__('Execution Speed'),
                'align'        => 'right',
                'type'         => 'options',
                'options'      => array(
                    Manager::EXECUTION_SPEED_FAST => Mage::helper('M2ePro')->__('Fast'),
                    Manager::EXECUTION_SPEED_SLOW => Mage::helper('M2ePro')->__('Slow')
                ),
                'width'        => '10%',
                'index'        => 'execution_speed',
                'filter_index' => 'execution_speed',
                'filter_condition_callback' => array($this, 'callbackFilterMatch'),
                'frame_callback' => array($this, 'callbackColumnSpeed')
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_controlPanel_inspection/getInspectionsGrid');
    }

    //########################################

    protected function callbackFilterLike($collection, $column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
        $value = $column->getFilter()->getValue();
        if ($value == null || empty($field)) {
            return;
        }

        $this->getCollection()->addFilter(
            $field, $value, Ess_M2ePro_Model_Collection_Custom::CONDITION_LIKE
        );
    }

    protected function callbackFilterMatch($collection, $column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
        $value = $column->getFilter()->getValue();
        if ($value == null || empty($field)) {
            return;
        }

        if ($value == self::NOT_SUCCESS_FILTER) {
            $field = 'need_attention';
            $value = '1';
        }

        $this->getCollection()->addFilter(
            $field, $value, Ess_M2ePro_Model_Collection_Custom::CONDITION_MATCH
        );
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection $inspection */
        $inspection = $row->getData('inspection');

        $value = <<<HTML
<span style="color: grey;">[{$inspection->getGroup()}]</span> {$value}
HTML;

        if (!$row->getData('description')) {
            return $value;
        }

        $toolTipIconSrc = $this->getSkinUrl('M2ePro/images/tool-tip-icon.png');
        $helpIconSrc = $this->getSkinUrl('M2ePro/images/help.png');

        return <<<HTML
{$value}
<div style="display: inline-block;">
    <img src="{$toolTipIconSrc}" class="tool-tip-image">
    <span class="tool-tip-message" style="font-size: 12px; display: none;">
        <img src="{$helpIconSrc}">
        <span>{$row->getData('description')}</span>
    </span>
</div>
HTML;
    }

    public function callbackColumnDetails($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection $inspection */
        $inspection = $row->getData('inspection');

        $html = '';
        foreach ($inspection->getResults() as $result) {
            $html .= '<div>';
            $html .= <<<HTML
{$this->getMarkupByResult($result->getState(), $result->getMessage())}
HTML;
            if ($result->getMetadata()) {
                $html .= <<<HTML
&nbsp;&nbsp;
<a href="javascript://" onclick="ControlPanelInspectionObj.showMetaData(this);">[{$this->__('details')}]</a>
<div class="no-display"><div>{$result->getMetadata()}</div></div>
HTML;
            }

            $html .= '</div>';
        }

        return $html;
    }

    public function callbackColumnState($value, $row, $column, $isExport)
    {
        return $this->getMarkupByResult($row->getData($column->getIndex()), $value);
    }

    public function callbackColumnSpeed($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection $inspection */
        $inspection = $row->getData('inspection');

        return <<<HTML
{$value} <span style="color: grey;">[{$inspection->getTimeToExecute()} sec.]</span>
HTML;
    }

    //########################################

    protected function getMarkupByResult($result, $text)
    {
        switch ($result) {
            case Result::STATE_ERROR:
                return "<span style='color: red; font-weight: bold;'>{$text}</span>";

            case Result::STATE_WARNING:
                return "<span style='color: darkorange; font-weight: bold;'>{$text}</span>";

            case Result::STATE_NOTICE:
                return "<span style='color: dodgerblue; font-weight: bold;'>{$text}</span>";

            case Result::STATE_SUCCESS:
                return "<span style='color: green; font-weight: bold;'>{$text}</span>";
        }

        return $text;
    }

    public function _toHtml()
    {
        $css = <<<HTML
<style>
.data tr {
height: 30px;
}
.data td {
vertical-align: inherit;
}
</style>
HTML;

        return $css . parent::_toHtml();
    }

    //########################################
}
