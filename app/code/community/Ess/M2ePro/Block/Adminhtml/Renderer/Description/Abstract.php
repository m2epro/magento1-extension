<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Renderer_Description_Abstract extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    protected function _construct()
    {
        parent::_construct();
        $this->setData('area', 'adminhtml');
    }

    // ####################################
}