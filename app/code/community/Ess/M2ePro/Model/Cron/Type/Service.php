<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Cron_Type_Service extends Ess_M2ePro_Model_Cron_Type_Abstract
{
    const MAX_INACTIVE_TIME = 300;

    private $requestAuthKey = NULL;
    private $requestConnectionId = NULL;

    //####################################

    protected function getType()
    {
        return Ess_M2ePro_Helper_Module_Cron::TYPE_SERVICE;
    }

    //####################################

    public function setRequestAuthKey($value)
    {
        $this->requestAuthKey = $value;
    }

    public function getRequestAuthKey()
    {
        return $this->requestAuthKey;
    }

    // -----------------------------------

    public function setRequestConnectionId($value)
    {
        $this->requestConnectionId = $value;
    }

    public function getRequestConnectionId()
    {
        return $this->requestConnectionId;
    }

    // -----------------------------------

    public function resetTasksStartFrom()
    {
        $this->resetTaskStartFrom('processing');
        $this->resetTaskStartFrom('servicing');
        $this->resetTaskStartFrom('synchronization');
    }

    //####################################

    protected function isDisabledByDeveloper()
    {
        return (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                              ->getGroupValue('/cron/service/','disabled');
    }

    protected function initialize()
    {
        parent::initialize();

        $helper = Mage::helper('M2ePro/Module_Cron');

        if (!$helper->isTypeService()) {

            $helper->setType(Ess_M2ePro_Helper_Module_Cron::TYPE_SERVICE);
            $helper->setLastTypeChange(Mage::helper('M2ePro')->getCurrentGmtDate());

            $this->resetTasksStartFrom();

        } else {
            $helper->isLastAccessMoreThan(self::MAX_INACTIVE_TIME) &&
                $this->resetTasksStartFrom();
        }
    }

    protected function isPossibleToRun()
    {
        return !is_null($this->getAuthKey()) &&
               !is_null($this->getRequestAuthKey()) &&
               !is_null($this->getRequestConnectionId()) &&
               $this->getAuthKey() == $this->getRequestAuthKey() &&
               parent::isPossibleToRun();
    }

    // -----------------------------------

    protected function beforeStart()
    {
        parent::beforeStart();
        $this->getOperationHistory()->setContentData('connection_id',$this->getRequestConnectionId());
    }

    //####################################

    private function getAuthKey()
    {
        return Mage::helper('M2ePro/Module')->getConfig()
                    ->getGroupValue('/cron/service/','auth_key');
    }

    private function resetTaskStartFrom($taskName)
    {
        $config = Mage::helper('M2ePro/Module')->getConfig();

        $startDate = new DateTime(Mage::helper('M2ePro')->getCurrentGmtDate(), new DateTimeZone('UTC'));
        $shift = 60 + rand(0,(int)$config->getGroupValue('/cron/task/'.$taskName.'/','interval'));
        $startDate->modify('+'.$shift.' seconds');

        $config->setGroupValue('/cron/task/'.$taskName.'/','start_from',$startDate->format('Y-m-d H:i:s'));
    }

    //####################################
}