<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_IndexController extends Mage_Core_Controller_Front_Action
{
    //########################################

    public function indexAction()
    {
        $this->_redirect(Mage::helper('M2ePro/Module_Support')->getPageRoute());
    }

    //########################################
}