<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_ControlPanel_Inspection_Result as Result;

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    const NOT_SUCCESS_FILTER = 'not-success';

    /** @var Ess_M2ePro_Model_ControlPanel_Inspection_Repository $repository */
    protected $repository;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->repository = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Repository');

        $this->setId('controlPanelInspectionGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = new Ess_M2ePro_Model_Collection_Custom();

        foreach ($this->repository->getDefinitions() as $definition) {
            $row = array(
                'id'          => $definition->getNick(),
                'title'       => $definition->getTitle(),
                'description' => $definition->getDescription(),
                'group'       => $definition->getGroup(),
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
                'header'                    => Mage::helper('M2ePro')->__('Title'),
                'align'                     => 'left',
                'type'                      => 'text',
                'width'                     => '20%',
                'index'                     => 'title',
                'filter_index'              => 'title',
                'filter_condition_callback' => array($this, 'callbackFilterLike'),
                'frame_callback'            => array($this, 'callbackColumnTitle')
            )
        );

        $this->addColumn(
            'details',
            array(
                'header'           => Mage::helper('M2ePro')->__('Details'),
                'align'            => 'left',
                'type'             => 'text',
                'column_css_class' => 'details',
                'width'            => '40%',
                'filter_index'     => false,
            )
        );

        $this->addColumn(
            'actions',
            array(
                'header'   => Mage::helper('M2ePro')->__('Actions'),
                'align'    => 'left',
                'width'    => '150px',
                'type'     => 'action',
                'index'    => 'actions',
                'filter'   => false,
                'sortable' => false,
                'getter'   => 'getId',
                'renderer' => 'M2ePro/adminhtml_grid_column_renderer_action',
                'actions'  => array(
                    'checkAction' => array(
                        'caption' => Mage::helper('M2ePro')->__('Check'),
                        'field'   => 'id',
                        'onclick' => 'ControlPanelInspectionObj.checkAction()',
                    )
                ),
            )
        );

        $this->addColumn(
            'id',
            array(
                'header'           => Mage::helper('M2ePro')->__('ID'),
                'align'            => 'right',
                'width'            => '100px',
                'type'             => 'text',
                'index'            => 'id',
                'column_css_class' => 'no-display id',//this sets a css class to the column row item
                'header_css_class' => 'no-display',//this sets a css class to the column header
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        $this->getMassactionBlock()->addItem(
            'checkAll',
            array(
                'label'    => $this->__('Run'),
                'url'      => '',
            )
        );

        return parent::_prepareMassaction();
    }

    //########################################

    protected function _addColumnFilterToCollection($column)
    {
        $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();

        if ($field === 'id') {
            return $this;
        }

        return parent::_addColumnFilterToCollection($column);
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
            $field,
            $value,
            Ess_M2ePro_Model_Collection_Custom::CONDITION_LIKE
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
            $field,
            $value,
            Ess_M2ePro_Model_Collection_Custom::CONDITION_MATCH
        );
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = <<<HTML
<span style="color: grey;">[{$row->getData('group')}]</span> {$value}
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

    //########################################

    public function _toHtml()
    {
        $urls = Mage::helper('M2ePro')->jsonEncode(
            array(
                'checkInspection' => $this->getUrl('M2ePro/adminhtml_controlPanel_Inspection/checkInspection')
            )
        );

        // Set ids to be able to use option "Select All"
        $ids = array();

        foreach ($this->repository->getDefinitions() as $definition) {
            $ids[] = $definition->getNick();
        }

        $allIdsStr = implode(",", $ids);

        $jsUrl = <<<JS
<script type="text/javascript">
M2ePro.url.add({$urls});

ControlPanelInspectionObj = new ControlPanelInspection('{$this->getId()}');

ControlPanelInspectionObj.afterInitPage();
ControlPanelInspectionObj.getGridMassActionObj().setGridIds('{$allIdsStr}');
</script>
JS;

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

        return parent::_toHtml() . $jsUrl . $css;
    }

    //########################################
}
