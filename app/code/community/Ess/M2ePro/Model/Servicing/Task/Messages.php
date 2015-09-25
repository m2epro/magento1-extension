<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Task_Messages extends Ess_M2ePro_Model_Servicing_Task
{
    // ########################################

    public function getPublicNick()
    {
        return 'messages';
    }

    // ########################################

    public function getRequestData()
    {
        return array();
    }

    public function processResponseData(array $data)
    {
        $this->updateMagentoMessages($data);
        $this->updateModuleMessages($data);
    }

    // ########################################

    private function updateMagentoMessages(array $messages)
    {
        $messages = array_filter($messages,array($this,'updateMagentoMessagesFilterMagentoMessages'));
        !is_array($messages) && $messages = array();

        $magentoTypes = array(
            Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_NOTICE =>
                Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE,
            Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_SUCCESS =>
                Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE,
            Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_WARNING =>
                Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR,
            Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_ERROR =>
                Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL
        );

        foreach ($messages as $message) {
            Mage::helper('M2ePro/Magento')->addGlobalNotification(
                $message['title'],
                $message['text'],
                $magentoTypes[$message['type']]
            );
        }
    }

    public function updateMagentoMessagesFilterMagentoMessages($message)
    {
        if (!isset($message['title']) || !isset($message['text']) || !isset($message['type'])) {
            return false;
        }

        if (!isset($message['is_global']) || !(bool)$message['is_global']) {
            return false;
        }

        return true;
    }

    // ########################################

    private function updateModuleMessages(array $messages)
    {
        $messages = array_filter($messages,array($this,'updateModuleMessagesFilterModuleMessages'));
        !is_array($messages) && $messages = array();

        Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/server/','messages',json_encode($messages)
        );
    }

    public function updateModuleMessagesFilterModuleMessages($message)
    {
        if (!isset($message['text']) || !isset($message['type'])) {
            return false;
        }

        if (isset($message['is_global']) && (bool)$message['is_global']) {
            return false;
        }

        return true;
    }

    // ########################################
}