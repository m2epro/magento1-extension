<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Logger extends Mage_Core_Helper_Abstract
{
    //########################################

    public function process($logData, $type = null, $sendToServer = true)
    {
        try {
            $info  = $this->getLogMessage($logData, $type);
            $info .= $this->getStackTraceInfo();
            $info .= $this->getCurrentUserActionInfo();

            $this->log($info, $type);

            if (!$sendToServer || !(bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                  ->getGroupValue('/debug/logging/', 'send_to_server')) {
                return;
            }

            $type = $type === null ? 'undefined' : $type;
            $info .= Mage::helper('M2ePro/Module_Support_Form')->getSummaryInfo();

            $this->send($info, $type);
        } catch (Exception $exceptionTemp) {
        }
    }

    //########################################

    protected function log($info, $type)
    {
        /** @var Ess_M2ePro_Model_Log_System $log */
        $log = Mage::getModel('M2ePro/Log_System');

        $log->setType($type === null ? 'Logging' : "{$type} Logging");
        $log->setDescription($info);

        $log->save();
    }

    //########################################

    protected function getLogMessage($logData, $type)
    {
        !is_string($logData) && $logData = print_r($logData, true);

        $logData = '[DATE] '.date('Y-m-d H:i:s', (int)gmdate('U')).PHP_EOL.
                   '[TYPE] '.$type.PHP_EOL.
                   '[MESSAGE] '.$logData.PHP_EOL.
                   str_repeat('#', 80).PHP_EOL.PHP_EOL;

        return $logData;
    }

    protected function getStackTraceInfo()
    {
        $exception = new Exception('');
        $stackTraceInfo = <<<TRACE
-------------------------------- STACK TRACE INFO --------------------------------
{$exception->getTraceAsString()}

TRACE;

        return $stackTraceInfo;
    }

    protected function getCurrentUserActionInfo()
    {
        $server = print_r(Mage::app()->getRequest()->getServer(), true);
        $get = print_r(Mage::app()->getRequest()->getQuery(), true);
        $post = print_r(Mage::app()->getRequest()->getPost(), true);

        $actionInfo = <<<ACTION
-------------------------------- ACTION INFO -------------------------------------
SERVER: {$server}
GET: {$get}
POST: {$post}

ACTION;

        return $actionInfo;
    }

    //########################################

    protected function send($logData, $type)
    {
        $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'logger', 'add', 'entity',
            array('info' => $logData,
            'type' => $type)
        );
        $dispatcherObject->process($connectorObj);
    }

    //########################################
}
