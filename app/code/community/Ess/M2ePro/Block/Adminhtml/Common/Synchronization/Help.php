<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Synchronization_Help extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/common/synchronization/help.phtml');
    }

    // ####################################
}