<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_CronController extends Mage_Core_Controller_Varien_Action
{
    //#############################################

    public function preDispatch()
    {
        $this->getLayout()->setArea('frontend');
        parent::preDispatch();
    }

    //#############################################

    public function indexAction()
    {
        $this->closeConnection();

        $cron = Mage::getModel('M2ePro/Cron_Type_Service');
        $cron->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

        $authKey = $this->getRequest()->getPost('auth_key',false);
        $authKey && $cron->setRequestAuthKey($authKey);

        $connectionId = $this->getRequest()->getPost('connection_id',false);
        $connectionId && $cron->setRequestConnectionId($connectionId);

        $cron->process();

        exit();
    }

    public function testAction()
    {
        exit('ok');
    }

    // --------------------------------------------

    public function resetAction()
    {
        Mage::getModel('M2ePro/Cron_Type_Service')->resetTasksStartFrom();
    }

    //#############################################

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

    //#############################################
}