<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Item_Ktype_Grid
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Item_Grid
{
    //########################################

    public function getMotorsType()
    {
        return Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_KTYPE;
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = new Ess_M2ePro_Model_Mysql4_Ebay_Motor_Ktypes_Collection('ktype');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn('ktype', array(
            'header' => Mage::helper('M2ePro')->__('kType'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'ktype',
            'width'  => '100px',
            'frame_callback' => array($this, 'callbackColumnIdentifier')
        ));

        $this->addColumn('make', array(
            'header' => Mage::helper('M2ePro')->__('Make'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'make',
            'width'  => '150px'
        ));

        $this->addColumn('model', array(
            'header' => Mage::helper('M2ePro')->__('Model'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'model',
            'width'  => '150px'
        ));

        $this->addColumn('variant', array(
            'header' => Mage::helper('M2ePro')->__('Variant'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'variant',
            'width'  => '150px'
        ));

        $this->addColumn('body_style', array(
            'header' => Mage::helper('M2ePro')->__('Body Style'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'body_style',
            'width'  => '150px'
        ));

        $this->addColumn('type', array(
            'header' => Mage::helper('M2ePro')->__('Type'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'type',
            'width'  => '150px'
        ));

        $this->addColumn('year', array(
            'header' => Mage::helper('M2ePro')->__('Year'),
            'align'  => 'left',
            'type'   => 'text',
            'width'  => '150px',
            'index'  => 'to_year',
            'filter_index' => 'from_year',
            'frame_callback'            => array($this, 'callbackYearColumn'),
            'filter_condition_callback' => array($this, 'yearColumnFilter'),
        ));

        $this->addColumn('engine', array(
            'header' => Mage::helper('M2ePro')->__('Engine'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'engine',
            'width'  => '100px',
            'frame_callback' => array($this, 'callbackNullableColumn')
        ));

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackYearColumn($value, $row, $column, $isExport)
    {
        return $row['from_year'] . ' - ' . $row['to_year'];
    }

    public function yearColumnFilter($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return $this;
        }

        /** @var Varien_Data_Collection_Db $collection */
        $collection->addFieldToFilter('from_year', array('to' => $value));
        $collection->addFieldToFilter('to_year', array('from' => $value));

        return $this;
    }

    //########################################
}