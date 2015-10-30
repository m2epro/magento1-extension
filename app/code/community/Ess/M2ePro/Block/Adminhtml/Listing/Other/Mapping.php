<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Other_Mapping extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/listing/other/mapping.phtml');
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'id'    => 'mapping_submit_button',
            'label' => Mage::helper('M2ePro')->__('Confirm'),
            'class' => 'mapping_submit_button submit'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('mapping_submit_button',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $this->setChild(
            'mapping_grid',
            $this->getLayout()->createBlock('M2ePro/adminhtml_listing_other_mapping_grid')
        );
        // ---------------------------------------

        parent::_beforeToHtml();
    }

    //########################################
}