<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    private $motorsType = null;

    private $productGridId = null;

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/ebay/motor/add.phtml');
    }

    protected function _beforeToHtml()
    {
        if (is_null($this->motorsType)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Compatibility type was not set.');
        }

        //------------------------------
        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Tabs $tabsBlock */
        $tabsBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_add_tabs');
        $tabsBlock->setMotorsType($this->getMotorsType());
        $this->setChild('motor_add_tabs', $tabsBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'style' => 'float: right;',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'EbayMotorsHandlerObj.closeInstruction();'
        );
        $confirmBtn = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('motor_confirm_btn', $confirmBtn);
        //------------------------------

        //------------------------------
        $data = array(
            'style' => 'margin-right: 5px',
            'label'   => Mage::helper('M2ePro')->__('Add'),
            'onclick' => 'EbayMotorsHandlerObj.updateMotorsData(0);'
        );
        $closeBtn = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('motor_add_btn', $closeBtn);
        //------------------------------

        //------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Override'),
            'onclick' => 'EbayMotorsHandlerObj.updateMotorsData(1);'
        );
        $closeBtn = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('motor_override_btn', $closeBtn);
        //------------------------------

        //------------------------------
        $data = array(
            'style' => 'float: right;',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'Windows.getFocusedWindow().close();'
        );
        $closeBtn = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('motor_close_btn', $closeBtn);
        //------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    public function wasInstructionShown()
    {
        return Mage::helper('M2ePro/Module')->getCacheConfig()
                    ->getGroupValue('/ebay/motors/','was_instruction_shown') != false;
    }

    //########################################

    public function setMotorsType($type)
    {
        $this->motorsType = $type;
        return $this;
    }

    public function getMotorsType()
    {
        return $this->motorsType;
    }

    // ---------------------------------------

    public function setProductGridId($gridId)
    {
        $this->productGridId = $gridId;
        return $this;
    }

    public function getProductGridId()
    {
        return $this->productGridId;
    }

    // ---------------------------------------

    public function isMotorsTypeKtype()
    {
        return Mage::helper('M2ePro/Component_Ebay_Motors')->isTypeBasedOnKtypes($this->getMotorsType());
    }

    public function isMotorsTypeEpid()
    {
        return Mage::helper('M2ePro/Component_Ebay_Motors')->isTypeBasedOnEpids($this->getMotorsType());
    }

    // Add Custom Compatible Vehicle
    //########################################

    public function getRecordColumns()
    {
        return $this->isMotorsTypeKtype() ? $this->getKtypeRecordColumns()
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
                   Ess_M2ePro_Helper_Component_Ebay_Motors::PRODUCT_TYPE_VEHICLE
                             => Mage::helper('M2ePro')->__('Car / Truck'),
                   Ess_M2ePro_Helper_Component_Ebay_Motors::PRODUCT_TYPE_MOTORCYCLE
                             => Mage::helper('M2ePro')->__('Motorcycle'),
                   Ess_M2ePro_Helper_Component_Ebay_Motors::PRODUCT_TYPE_ATV
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

    //########################################
}