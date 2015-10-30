<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Renderer_Description_Abstract extends Mage_Adminhtml_Block_Widget
{
    //########################################

    protected function _construct()
    {
        parent::_construct();
        $this->setData('area', 'adminhtml');
    }

    //########################################
}