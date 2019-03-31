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
        $typesMapping = array(
            Mage_Core_Model_Message::NOTICE  => AdminNotification::SEVERITY_NOTICE,
            Mage_Core_Model_Message::SUCCESS => AdminNotification::SEVERITY_MINOR,
            Mage_Core_Model_Message::WARNING => AdminNotification::SEVERITY_MAJOR,
            Mage_Core_Model_Message::ERROR   => AdminNotification::SEVERITY_CRITICAL
        );

        $dataForAdd = array(
            'title'       => $issue->getTitle(),
            'description' => strip_tags($issue->getText()),
            'url'         => !is_null($issue->getUrl()) ? $issue->getUrl()
                                                        : 'https://m2epro.com/?' . sha1($issue->getTitle()),
            'severity'    => isset($typesMapping[$issue->getType()]) ? $typesMapping[$issue->getType()]
                                                                     : AdminNotification::SEVERITY_CRITICAL,
            'date_added'  => now()
        );

        Mage::getModel('adminnotification/inbox')->parse(array($dataForAdd));
    }

    //########################################
}