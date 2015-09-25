<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Marketplace_Tabs extends Ess_M2ePro_Block_Adminhtml_Common_Component_Tabs
{
    // ########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/common/component/tabs/linktabs.phtml');
    }

    // ########################################
}