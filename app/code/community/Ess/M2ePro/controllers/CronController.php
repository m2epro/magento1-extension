<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_CronController extends Mage_Core_Controller_Varien_Action
{
    //########################################

    public function preDispatch()
    {
        $this->getLayout()->setArea(Mage_Core_Model_App_Area::AREA_FRONTEND);
        parent::preDispatch();
    }

    //########################################

    public function indexAction()
    {
        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Service');

        $authKey = $this->getRequest()->getPost('auth_key', false);
        $authKey && $cronRunner->setRequestAuthKey($authKey);

        $connectionId = $this->getRequest()->getPost('connection_id', false);
        $connectionId && $cronRunner->setRequestConnectionId($connectionId);

        $cronRunner->process();

        $this->getResponse()->setBody('processing...');
        return $this->getResponse();
    }

    public function testAction()
    {
        $installationKey = Mage::helper('M2ePro/Module')->getInstallationKey();
        if (empty($installationKey)) {
            return $this->getResponse()->setBody('ok');
        }

        return $this->getResponse()->setBody($installationKey);
    }

    // ---------------------------------------

    public function resetAction()
    {
        Mage::getModel('M2ePro/Cron_Runner_Service')->resetTasksStartFrom();
    }

    //########################################
}
