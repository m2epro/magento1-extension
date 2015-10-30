<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Logger extends Mage_Core_Helper_Abstract
{
    //########################################

    public function process($logData, $type = NULL, $sendToServer = true)
    {
        try {

            $this->log($logData, $type);

            if (!$sendToServer || !(bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                  ->getGroupValue('/debug/logging/', 'send_to_server')) {
                return;
            }

            $type = is_null($type) ? 'undefined' : $type;

            $logData = $this->prepareLogMessage($logData, $type);
            $logData .= $this->getCurrentUserActionInfo();
            $logData .= Mage::helper('M2ePro/Module_Support_Form')->getSummaryInfo();

            $this->send($logData, $type);

        } catch (Exception $exceptionTemp) {}
    }

    //########################################

    private function prepareLogMessage($logData, $type)
    {
        !is_string($logData) && $logData = print_r($logData, true);

        $logData = '[DATE] '.date('Y-m-d H:i:s',(int)gmdate('U')).PHP_EOL.
                   '[TYPE] '.$type.PHP_EOL.
                   '[MESSAGE] '.$logData.PHP_EOL.
                   str_repeat('#',80).PHP_EOL.PHP_EOL;

        return $logData;
    }

    private function log($logData, $type)
    {
        /** @var Ess_M2ePro_Model_Log_System $log */
        $log = Mage::getModel('M2ePro/Log_System');

        $log->setType(is_null($type) ? 'Logging' : "{$type} Logging");
        $log->setDescription(is_string($logData) ? $logData : print_r($logData, true));

        $log->save();
    }

    //########################################

    private function getCurrentUserActionInfo()
    {
        $server = isset($_SERVER) ? print_r($_SERVER, true) : '';
        $get = isset($_GET) ? print_r($_GET, true) : '';
        $post = isset($_POST) ? print_r($_POST, true) : '';

        $actionInfo = <<<ACTION
-------------------------------- ACTION INFO -------------------------------------
SERVER: {$server}
GET: {$get}
POST: {$post}

ACTION;

        return $actionInfo;
    }

    //########################################

    private function send($logData, $type)
    {
        $dispatcherObject = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('logger', 'add', 'entity',
                                                               array('info' => $logData,
                                                                     'type' => $type));
        $dispatcherObject->process($connectorObj);
    }

    //########################################
}