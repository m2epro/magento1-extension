<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Mage_AdminNotification_Model_Inbox as AdminNotification;

class Ess_M2ePro_Model_Issue_Notification_Channel_Magento_GlobalMessage
    implements Ess_M2ePro_Model_Issue_Notification_Channel_Interface
{
    //########################################

    public function addMessage(Ess_M2ePro_Model_Issue_Object $issue)
    {
        $dataForAdd = array(
            'title'       => $issue->getTitle(),
            'description' => strip_tags($issue->getText()),
            'url'         => $issue->getUrl() !== null ? $issue->getUrl()
                                                       : 'https://m2epro.com/?' . sha1($issue->getTitle()),
            'severity'    => $this->recognizeSeverity($issue),
            'date_added'  => now()
        );

        Mage::getModel('adminnotification/inbox')->parse(array($dataForAdd));
    }

    //########################################

    protected function recognizeSeverity(Ess_M2ePro_Model_Issue_Object $issue)
    {
        $notice = array(
            Mage_Core_Model_Message::NOTICE,
            Mage_Core_Model_Message::SUCCESS,
            Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_NOTICE,
            Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_SUCCESS
        );

        if (in_array($issue->getType(), $notice, true)) {
            return AdminNotification::SEVERITY_NOTICE;
        }

        $warning = array(
            Mage_Core_Model_Message::WARNING,
            Ess_M2ePro_Helper_Module::SERVER_MESSAGE_TYPE_WARNING
        );

        if (in_array($issue->getType(), $warning, true)) {
            return AdminNotification::SEVERITY_MINOR;
        }

        return AdminNotification::SEVERITY_CRITICAL;
    }

    //########################################
}
