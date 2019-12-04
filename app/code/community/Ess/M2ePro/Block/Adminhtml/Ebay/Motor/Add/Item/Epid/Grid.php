<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Item_Epid_Grid
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Item_Grid
{
    //########################################

    protected function _prepareCollection()
    {
        $scope = Mage::helper('M2ePro/Component_Ebay_Motors')->getEpidsScopeByType($this->getMotorsType());
        $collection = new Ess_M2ePro_Model_Resource_Ebay_Motor_Epids_Collection('epid', $scope);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn(
            'epid', array(
            'header' => Mage::helper('M2ePro')->__('ePID'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'epid',
            'width'  => '100px',
            'frame_callback' => array($this, 'callbackColumnIdentifier')
            )
        );

        $this->addColumn(
            'product_type', array(
            'header' => Mage::helper('M2ePro')->__('Type'),
            'align'  => 'left',
            'type'   => 'options',
            'index'  => 'product_type',
            'options'  => array(
                Ess_M2ePro_Helper_Component_Ebay_Motors::PRODUCT_TYPE_VEHICLE
                    => Mage::helper('M2ePro')->__('Car / Truck'),
                Ess_M2ePro_Helper_Component_Ebay_Motors::PRODUCT_TYPE_MOTORCYCLE
                    => Mage::helper('M2ePro')->__('Motorcycle'),
                Ess_M2ePro_Helper_Component_Ebay_Motors::PRODUCT_TYPE_ATV
                    => Mage::helper('M2ePro')->__('ATV / Snowmobiles'),
            )
            )
        );

        $this->addColumn(
            'make', array(
            'header' => Mage::helper('M2ePro')->__('Make'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'make',
            'width'  => '150px'
            )
        );

        $this->addColumn(
            'model', array(
            'header' => Mage::helper('M2ePro')->__('Model'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'model',
            'width'  => '150px'
            )
        );

        $this->addColumn(
            'submodel', array(
            'header' => Mage::helper('M2ePro')->__('Submodel'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'submodel',
            'width'  => '100px',
            'frame_callback' => array($this, 'callbackNullableColumn')
            )
        );

        $this->addColumn(
            'year', array(
            'header' => Mage::helper('M2ePro')->__('Year'),
            'align'  => 'left',
            'type'   => 'number',
            'index'  => 'year',
            'width'  => '100px'
            )
        );

        $this->addColumn(
            'trim', array(
            'header' => Mage::helper('M2ePro')->__('Trim'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'trim',
            'width'  => '100px',
            'frame_callback' => array($this, 'callbackNullableColumn')
            )
        );

        $this->addColumn(
            'engine', array(
            'header' => Mage::helper('M2ePro')->__('Engine'),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'engine',
            'width'  => '100px',
            'frame_callback' => array($this, 'callbackNullableColumn')
            )
        );

        $this->addColumn(
            'street_name', array(
                'header' => Mage::helper('M2ePro')->__('Street Name'),
                'align'  => 'left',
                'type'   => 'text',
                'index'  => 'street_name',
                'width'  => '100px',
                'frame_callback' => array($this, 'callbackNullableColumn')
            )
        );

        return parent::_prepareColumns();
    }

    //########################################
}
