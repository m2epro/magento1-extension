<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Create_Breadcrumb
    extends Ess_M2ePro_Block_Adminhtml_Widget_Breadcrumb
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->setId('amazonListingBreadcrumb');

        $this->setSteps(
            array(
                array(
                    'id' => 1,
                    'title' => Mage::helper('M2ePro')->__('Step 1'),
                    'description' => Mage::helper('M2ePro')->__('General Settings')
                ),
                array(
                    'id' => 2,
                    'title' => Mage::helper('M2ePro')->__('Step 2'),
                    'description' => Mage::helper('M2ePro')->__('Selling Settings')
                ),
                array(
                    'id' => 3,
                    'title' => Mage::helper('M2ePro')->__('Step 3'),
                    'description' => Mage::helper('M2ePro')->__('Product Identifiers')
                )
            )
        );
    }

    //########################################
}
