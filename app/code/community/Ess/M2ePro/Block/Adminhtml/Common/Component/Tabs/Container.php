<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Common_Component_Tabs_Container
    extends Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract
{
    // ########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/common/component/container.phtml');
    }

    // ########################################
}