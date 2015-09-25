<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Cron_Type_Magento extends Ess_M2ePro_Model_Cron_Type_Abstract
{
    const MIN_DISTRIBUTION_EXECUTION_TIME = 300;
    const MAX_DISTRIBUTION_WAIT_INTERVAL = 59;

    //####################################

    protected function getType()
    {
        return Ess_M2ePro_Helper_Module_Cron::TYPE_MAGENTO;
    }

    //####################################

    protected function isDisabledByDeveloper()
    {
        return (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                              ->getGroupValue('/cron/magento/','disabled');
    }

    protected function initialize()
    {
        usleep(rand(0,2000000));

        parent::initialize();

        $helper = Mage::helper('M2ePro/Module_Cron');
        $maxServiceInactiveTime = Ess_M2ePro_Model_Cron_Type_Service::MAX_INACTIVE_TIME;

        if (!$helper->isTypeMagento() &&
            $helper->isLastRunMoreThan($maxServiceInactiveTime) &&
            !$this->getLockItem()->isExist()) {

            $helper->setType(Ess_M2ePro_Helper_Module_Cron::TYPE_MAGENTO);
            $helper->setLastTypeChange(Mage::helper('M2ePro')->getCurrentGmtDate());
        }
    }

    protected function isPossibleToRun()
    {
        return is_null(Mage::helper('M2ePro/Data_Global')->getValue('cron_running')) &&
               parent::isPossibleToRun();
    }

    // -----------------------------------

    protected function beforeStart()
    {
        Mage::helper('M2ePro/Data_Global')->setValue('cron_running',true);
        parent::beforeStart();
        $this->distributeLoadIfNeed();
    }

    protected function afterEnd()
    {
        parent::afterEnd();
        Mage::helper('M2ePro/Data_Global')->unsetValue('cron_running');
    }

    //####################################

    private function distributeLoadIfNeed()
    {
        if (Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {
            return;
        }

        $maxExecutionTime = (int)@ini_get('max_execution_time');

        if ($maxExecutionTime <= 0 || $maxExecutionTime < self::MIN_DISTRIBUTION_EXECUTION_TIME) {
            return;
        }

        sleep(rand(0,self::MAX_DISTRIBUTION_WAIT_INTERVAL));
        $this->getLockItem()->activate();
    }

    //####################################
}