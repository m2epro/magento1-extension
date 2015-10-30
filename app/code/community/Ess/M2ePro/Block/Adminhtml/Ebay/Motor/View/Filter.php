<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View_Filter extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    private $listingProductId;

    private $motorsType;

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/ebay/motor/view/filter.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View_Filter_Grid $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_view_filter_grid');
        $block->setListingProductId($this->getListingProductId());
        $block->setMotorsType($this->getMotorsType());
        $this->setChild('view_filter_grid', $block);
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

    /**
     * @return null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getListingProductId()
    {
        if (is_null($this->listingProductId)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Listing Product ID was not set.');
        }

        return $this->listingProductId;
    }

    /**
     * @param null $listingProductId
     */
    public function setListingProductId($listingProductId)
    {
        $this->listingProductId = $listingProductId;
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
}