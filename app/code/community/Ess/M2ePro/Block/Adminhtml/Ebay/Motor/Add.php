<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    private $compatibilityType = null;

    private $productGridId = null;

    // ##########################################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/ebay/motor/add.phtml');
    }

    protected function _beforeToHtml()
    {
        if (is_null($this->compatibilityType)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Compatibility type was not set.');
        }

        //------------------------------
        $data = array(
            'id'      => 'add_custom_compatibility_record_button',
            'label'   => Mage::helper('M2ePro')->__('Add Custom Compatible Vehicle'),
            'class'   => 'success',
            'onclick' => 'EbayMotorCompatibilityHandlerObj.openAddRecordPopup()'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('add_custom_compatibility_record_button', $buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }

    // ##########################################################

    public function setCompatibilityType($type)
    {
        $this->compatibilityType = $type;
        return $this;
    }

    public function getCompatibilityType()
    {
        return $this->compatibilityType;
    }

    // ----------------------------------------------------------

    public function setProductGridId($gridId)
    {
        $this->productGridId = $gridId;
        return $this;
    }

    public function getProductGridId()
    {
        return $this->productGridId;
    }

    // ----------------------------------------------------------

    public function isCompatibilityTypeKtype()
    {
        return $this->getCompatibilityType() == Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_KTYPE;
    }

    public function isCompatibilityTypeEpid()
    {
        return $this->getCompatibilityType() == Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_SPECIFIC;
    }

    // ----------------------------------------------------------

    public function getCompatibilityGridId()
    {
        $gridBlockName = '';

        $this->isCompatibilityTypeEpid()  && $gridBlockName = 'M2ePro/adminhtml_ebay_motor_add_specific_grid';
        $this->isCompatibilityTypeKtype() && $gridBlockName = 'M2ePro/adminhtml_ebay_motor_add_ktype_grid';

        if (empty($gridBlockName)) {
            return null;
        }

        return $this->getLayout()->createBlock($gridBlockName)->getId();
    }

    // -- Add Custom Compatible Vehicle
    // ##########################################################

    public function getRecordColumns()
    {
        return $this->isCompatibilityTypeKtype() ? $this->getKtypeRecordColumns()
                                                 : $this->getEpidRecordColumns();
    }

    private function getEpidRecordColumns()
    {
       return array(
           array(
               'name'        => 'epid',
               'title'       => 'ePID',
               'is_required' => true
           ),
           array(
               'name'        => 'product_type',
               'title'       => 'Type',
               'is_required' => true,
               'options'     => array(
                   Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::PRODUCT_TYPE_VEHICLE
                             => Mage::helper('M2ePro')->__('Car / Truck'),
                   Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::PRODUCT_TYPE_MOTORCYCLE
                             => Mage::helper('M2ePro')->__('Motorcycle'),
                   Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::PRODUCT_TYPE_ATV
                             => Mage::helper('M2ePro')->__('ATV / Snowmobiles'),
               )
           ),
           array(
               'name'        => 'make',
               'title'       => 'Make',
               'is_required' => true
           ),
           array(
               'name'        => 'model',
               'title'       => 'Model',
               'is_required' => true
           ),
           array(
               'name'        => 'submodel',
               'title'       => 'Submodel',
               'is_required' => false
           ),
           array(
               'name'        => 'year',
               'title'       => 'Year',
               'is_required' => true,
               'type'        => 'numeric'
           ),
           array(
               'name'        => 'trim',
               'title'       => 'Trim',
               'is_required' => false
           ),
           array(
               'name'        => 'engine',
               'title'       => 'Engine',
               'is_required' => false
           ),
       );
    }

    private function getKtypeRecordColumns()
    {
        return array(
            array(
                'name'        => 'ktype',
                'title'       => 'kType',
                'is_required' => true
            ),
            array(
                'name'        => 'make',
                'title'       => 'Make',
                'is_required' => false
            ),
            array(
                'name'        => 'model',
                'title'       => 'Model',
                'is_required' => false
            ),
            array(
                'name'        => 'variant',
                'title'       => 'Variant',
                'is_required' => false
            ),
            array(
                'name'        => 'body_style',
                'title'       => 'Body Style',
                'is_required' => false
            ),
            array(
                'name'        => 'type',
                'title'       => 'Type',
                'is_required' => false
            ),
            array(
                'name'        => 'from_year',
                'title'       => 'Year From',
                'is_required' => false,
                'type'        => 'numeric'
            ),
            array(
                'name'        => 'to_year',
                'title'       => 'Year To',
                'is_required' => false,
                'type'        => 'numeric'
            ),
            array(
                'name'        => 'engine',
                'title'       => 'Engine',
                'is_required' => false
            )
        );
    }

    // ##########################################################
}