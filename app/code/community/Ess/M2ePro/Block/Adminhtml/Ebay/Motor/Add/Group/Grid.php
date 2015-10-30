<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Group_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayMotorAddTabGroupGrid');

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        //------------------------------
    }

    //------------------------------

    protected function _prepareCollection()
    {
        /** @var Ess_M2ePro_Model_Mysql4_Ebay_Motor_Group_Collection $collection */
        $collection = Mage::getModel('M2ePro/Ebay_Motor_Group')->getCollection();
        $collection->addFieldToFilter('type', array('=' => $this->getMotorsType()));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header'       => Mage::helper('M2ePro')->__('Title'),
            'align'        => 'left',
            'type'         => 'text',
            'index'        => 'title',
            'filter_index' => 'title',
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('mode', array(
            'header'       => Mage::helper('M2ePro')->__('Type'),
            'width'        => '150px',
            'align'        => 'left',
            'type'         => 'options',
            'index'        => 'mode',
            'filter_index' => 'mode',
            'options' => array(
                Ess_M2ePro_Model_Ebay_Motor_Group::MODE_ITEM    => $this->getItemsColumnTitle(),
                Ess_M2ePro_Model_Ebay_Motor_Group::MODE_FILTER  => Mage::helper('M2ePro')->__('Filters'),
            ),
            'frame_callback' => array($this, 'callbackColumnMode')
        ));

        $this->addColumn('items', array(
            'header'       => Mage::helper('M2ePro')->__('Amount'),
            'width'        => '60px',
            'align'        => 'center',
            'type'         => 'text',
            'sortable'     => false,
            'filter'       => false,
            'index'        => 'items_data',
            'frame_callback' => array($this, 'callbackColumnItems')
        ));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // Set mass-action
        //--------------------------------
        $this->getMassactionBlock()->addItem('select', array(
            'label'   => Mage::helper('M2ePro')->__('Select'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('removeGroup', array(
            'label'   => Mage::helper('M2ePro')->__('Remove'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    public function getMassactionBlockName()
    {
        // this is required for correct work of massaction js
        return 'M2ePro/adminhtml_grid_massaction';
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        return $value;
    }

    public function callbackColumnMode($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_Ebay_Motor_Group $row */

        if ($row->isModeItem()) {
            return $this->getItemsColumnTitle();
        }

        if ($row->isModeFilter()) {
            return Mage::helper('M2ePro')->__('Filters');
        }

        return $value;
    }

    public function callbackColumnItems($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_Ebay_Motor_Group $row */

        if ($row->isModeItem()) {
            $itemsCount = count($row->getItems());
            $title = Mage::helper('M2ePro')->escapeHtml(
                Mage::helper('M2ePro')->__('Vew Group '.$this->getItemsColumnTitle())
            );
        } else {
            $itemsCount = count($row->getFiltersIds());
            $title = Mage::helper('M2ePro')->escapeHtml(
                Mage::helper('M2ePro')->__('Vew Group Filters')
            );
        }

        return <<<HTML
<a onclick="EbayMotorAddGroupGridHandlerObj.viewGroupContentPopup({$row->getId()}, '{$title}');"
    href="javascript:void(0)">
    {$itemsCount}
</a>
HTML;
    }

    //########################################

    protected function _toHtml()
    {
        $additionalHtml = <<<HTML
<style type="text/css">
    #{$this->getId()} table td, #{$this->getId()} table th {
        padding: 5px;
    }
</style>
HTML;

        $additionalHtml .= '<script type="text/javascript">';

        if ($this->canDisplayContainer()) {
            $additionalHtml .= <<<JS
EbayMotorAddGroupGridHandlerObj = new EbayMotorAddGroupGridHandler('{$this->getId()}');
JS;
        }

        $additionalHtml .= <<<JS
EbayMotorAddGroupGridHandlerObj.afterInitPage();
JS;

        $additionalHtml .= '</script>';

        return '<div style="height: 410px; overflow: auto;">' .
            parent::_toHtml()
            . '</div>' .
            $additionalHtml;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_motor/addGroupGrid', array(
            '_current' => true
        ));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    public function setMotorsType($motorsType)
    {
        $this->motorsType = $motorsType;
    }

    public function getMotorsType()
    {
        if (is_null($this->motorsType)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Motors type not set.');
        }

        return $this->motorsType;
    }

    //########################################

    public function getItemsColumnTitle()
    {
        if ($this->getMotorsType() == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID) {
            return Mage::helper('M2ePro')->__('ePID(s)');
        }

        return Mage::helper('M2ePro')->__('kType(s)');
    }

    //########################################
}