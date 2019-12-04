<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Widget_Grid_ModeSwitcher_Log
    extends Ess_M2ePro_Block_Adminhtml_Widget_Grid_ModeSwitcher
{
    //########################################

    protected function getUrlParams()
    {
        $params = parent::getUrlParams();

        $params['_current'] = true;

        return $params;
    }

    //########################################
}
