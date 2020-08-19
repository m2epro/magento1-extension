<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Log_Order extends Ess_M2ePro_Block_Adminhtml_Log_Order_AbstractContainer
{
    //########################################

    protected function getComponentMode()
    {
        return Ess_M2ePro_Helper_View_Amazon::NICK;
    }

    //########################################
}
