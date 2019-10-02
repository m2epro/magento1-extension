<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_IndexController extends Mage_Core_Controller_Front_Action
{
    //########################################

    public function indexAction()
    {
        return $this->getResponse()->setRedirect(Mage::getBaseUrl());
    }

    //########################################
}
