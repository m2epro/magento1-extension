<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Specific_Grid extends Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Grid
{
    // ##########################################################

    public function getCompatibilityType()
    {
        return Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_SPECIFIC;
    }

    // ##########################################################

    protected function _prepareCollection()
    {
        $collection = new Ess_M2ePro_Model_Mysql4_Ebay_Motor_Specifics_Collection('epid');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    // ##########################################################

    protected function _prepareColumns()
    {
        $this->addColumn('epid', array(
            'header' => Mage::helper('M2ePro')->__('ePID'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'epid',
            'width'  => '100px',
            'frame_callback' => array($this, 'callbackColumnIdentifier')
        ));

        $this->addColumn('product_type', array(
            'header' => Mage::helper('M2ePro')->__('Type'),
            'align'  => 'left',
            'type'   => 'options',
            'index'  => 'product_type',
            'options'  => array(
                Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::PRODUCT_TYPE_VEHICLE
                    => Mage::helper('M2ePro')->__('Car / Truck'),
                Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::PRODUCT_TYPE_MOTORCYCLE
                    => Mage::helper('M2ePro')->__('Motorcycle'),
                Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::PRODUCT_TYPE_ATV
                    => Mage::helper('M2ePro')->__('ATV / Snowmobiles'),
            )
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

        $this->addColumn('submodel', array(
            'header' => Mage::helper('M2ePro')->__('Submodel'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'submodel',
            'width'  => '100px',
            'frame_callback' => array($this, 'callbackNullableColumn')
        ));

        $this->addColumn('year', array(
            'header' => Mage::helper('M2ePro')->__('Year'),
            'align'  => 'left',
            'type'   => 'number',
            'index'  => 'year',
            'width'  => '100px'
        ));

        $this->addColumn('trim', array(
            'header' => Mage::helper('M2ePro')->__('Trim'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'trim',
            'width'  => '100px',
            'frame_callback' => array($this, 'callbackNullableColumn')
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

    //##############################################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/adminhtml_ebay_listing/motorSpecificGrid', array('listing_id' => $this->getListingId())
        );
    }

    //##############################################################
}