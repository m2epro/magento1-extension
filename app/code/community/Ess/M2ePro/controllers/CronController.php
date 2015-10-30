<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_CronController extends Mage_Core_Controller_Varien_Action
{
    //########################################

    public function preDispatch()
    {
        $this->getLayout()->setArea('frontend');
        parent::preDispatch();
    }

    //########################################

    public function indexAction()
    {
        $this->closeConnection();

        $cronRunner = Mage::getModel('M2ePro/Cron_Runner_Service');

        $authKey = $this->getRequest()->getPost('auth_key',false);
        $authKey && $cronRunner->setRequestAuthKey($authKey);

        $connectionId = $this->getRequest()->getPost('connection_id',false);
        $connectionId && $cronRunner->setRequestConnectionId($connectionId);

        $cronRunner->process();

        exit();
    }

    public function testAction()
    {
        exit('ok');
    }

    // ---------------------------------------

    public function resetAction()
    {
        Mage::getModel('M2ePro/Cron_Runner_Service')->resetTasksStartFrom();
    }

    //########################################

    private function closeConnection()
    {
        @ob_end_clean();
        ob_start();

        ignore_user_abort(true);
        echo 'processing...';

        header('Connection: Close');
        header('Content-Length: '.ob_get_length());

        while (ob_get_level()) {
            if (!$result = @ob_end_flush()) {
                break;
            }
        }

        @flush();

        $this->getResponse()->headersSentThrowsException = false;
    }

    //########################################
}