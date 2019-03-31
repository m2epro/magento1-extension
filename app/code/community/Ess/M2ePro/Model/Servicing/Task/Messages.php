<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_Messages extends Ess_M2ePro_Model_Servicing_Task
{
    //########################################

    /**
     * @return string
     */
    public function getPublicNick()
    {
        return 'messages';
    }

    //########################################

    /**
     * @return array
     */
    public function getRequestData()
    {
        return array();
    }

    public function processResponseData(array $data)
    {
        $this->updateMagentoMessages($data);
        $this->updateModuleMessages($data);
    }

    //########################################

    private function updateMagentoMessages(array $messages)
    {
        $messages = array_filter($messages, array($this, 'updateMagentoMessagesFilterMagentoMessages'));
        !is_array($messages) && $messages = array();

        /** @var Ess_M2ePro_Model_Issue_Notification_Channel_Magento_GlobalMessage $notificationChannel */
        $notificationChannel = Mage::getModel('M2ePro/Issue_Notification_Channel_Magento_GlobalMessage');

        foreach ($messages as $messageData) {

            /** @var Ess_M2ePro_Model_Issue_Object $issue */
            $issue = Mage::getModel('M2ePro/Issue_Object', $messageData);
            $notificationChannel->addMessage($issue);
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

    //########################################

    private function updateModuleMessages(array $messages)
    {
        $messages = array_filter($messages, array($this, 'updateModuleMessagesFilterModuleMessages'));
        !is_array($messages) && $messages = array();

        Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/server/',
            'messages',
            Mage::helper('M2ePro')->jsonEncode($messages)
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

    //########################################
}