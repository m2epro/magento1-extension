<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Moving_Help extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/listing/moving/help.phtml');
    }

    public function getComponentTitle()
    {
        return Mage::helper('M2ePro/Component')->getComponentTitle($this->getData('component_mode'));
    }

    //########################################
}
