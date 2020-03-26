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
    const SERVER_TYPE_NOTICE  = 0;
    const SERVER_TYPE_ERROR   = 1;
    const SERVER_TYPE_WARNING = 2;
    const SERVER_TYPE_SUCCESS = 3;

    //########################################

    public function addMessage(Ess_M2ePro_Model_Issue_Object $issue)
    {
        switch ($issue->getType()) {
            case Mage_Core_Model_Message::NOTICE:
            case Mage_Core_Model_Message::SUCCESS:
            case self::SERVER_TYPE_NOTICE:
            case self::SERVER_TYPE_SUCCESS:
                $severity = AdminNotification::SEVERITY_NOTICE;
                break;

            case Mage_Core_Model_Message::WARNING:
            case self::SERVER_TYPE_WARNING:
                $severity = AdminNotification::SEVERITY_MINOR;
                break;

            default:
            case Mage_Core_Model_Message::ERROR:
            case self::SERVER_TYPE_ERROR:
                $severity = AdminNotification::SEVERITY_CRITICAL;
                break;
        }

        $dataForAdd = array(
            'title'       => $issue->getTitle(),
            'description' => strip_tags($issue->getText()),
            'url'         => $issue->getUrl() !== null ? $issue->getUrl()
                                                        : 'https://m2epro.com/?' . sha1($issue->getTitle()),
            'severity'    => $severity,
            'date_added'  => now()
        );

        Mage::getModel('adminnotification/inbox')->parse(array($dataForAdd));
    }

    //########################################
}
