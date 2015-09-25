<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Support_Results extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('supportSearchResults');
        $this->setTemplate('M2ePro/support/results.phtml');
    }

    // ########################################
}