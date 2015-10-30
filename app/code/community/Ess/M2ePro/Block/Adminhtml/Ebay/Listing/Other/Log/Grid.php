<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Other_Log_Grid extends Ess_M2ePro_Block_Adminhtml_Listing_Other_Log_Grid
{
    //########################################

    protected function getColumnTitles()
    {
        return array(
            'create_date' => Mage::helper('M2ePro')->__('Creation Date'),
            'identifier' => Mage::helper('M2ePro')->__('Item ID'),
            'title' => Mage::helper('M2ePro')->__('Title'),
            'action' => Mage::helper('M2ePro')->__('Action'),
            'description' => Mage::helper('M2ePro')->__('Description'),
            'initiator' => Mage::helper('M2ePro')->__('Run Mode'),
            'type' => Mage::helper('M2ePro')->__('Type'),
        );
    }

    //########################################

    protected function getActionTitles()
    {
        return Mage::getModel('M2ePro/Listing_Other_Log')->getActionsTitles();
    }

    //########################################
}