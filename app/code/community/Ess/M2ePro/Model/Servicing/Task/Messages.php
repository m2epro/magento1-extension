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

    protected function updateMagentoMessages(array $messages)
    {
        $messages = array_filter($messages, function ($message) {
            return isset($message['is_global']) && (bool)$message['is_global'];
        });

        /** @var Ess_M2ePro_Model_Issue_Notification_Channel_Magento_GlobalMessage $notificationChannel */
        $notificationChannel = Mage::getModel('M2ePro/Issue_Notification_Channel_Magento_GlobalMessage');

        foreach ($messages as $messageData) {

            /** @var Ess_M2ePro_Model_Issue_Object $issue */
            $issue = Mage::getModel('M2ePro/Issue_Object', $messageData);
            $notificationChannel->addMessage($issue);
        }
    }

    protected function updateModuleMessages(array $messages)
    {
        $messages = array_filter($messages, function ($message) {
            return !isset($message['is_global']) || !(bool)$message['is_global'];
        });

        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->loadByKey('/server/messages/');

        $registry->setValue($messages);
        $registry->save();
    }

    //########################################
}
