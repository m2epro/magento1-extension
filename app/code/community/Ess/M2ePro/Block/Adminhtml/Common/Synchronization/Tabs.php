<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Synchronization_Tabs extends Ess_M2ePro_Block_Adminhtml_Common_Component_Tabs
{
    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/common/component/tabs/linktabs.phtml');
    }

    //########################################
}