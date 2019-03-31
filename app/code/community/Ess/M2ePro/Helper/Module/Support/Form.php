<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Support_Form extends Mage_Core_Helper_Abstract
{
    //########################################

    public function send($component, $fromEmail, $fromName, $subject, $description, $severity)
    {
        $attachments = array();

        if (isset($_FILES['files'])) {
            foreach ($_FILES['files']['name'] as $key => $uploadFileName) {
                if ('' == $uploadFileName) {
                    continue;
                }

                $realName = $uploadFileName;
                $tempPath = $_FILES['files']['tmp_name'][$key];
                $mimeType = $_FILES['files']['type'][$key];

                $attachment = new Zend_Mime_Part(file_get_contents($tempPath));
                $attachment->type        = $mimeType;
                $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                $attachment->encoding    = Zend_Mime::ENCODING_BASE64;
                $attachment->filename    = $realName;

                $attachments[] = $attachment;
            }
        }

        $toEmail = Mage::helper('M2ePro/Module_Support')->getContactEmail();
        $componentTitle = Mage::helper('M2ePro/Component')->getComponentTitle($component);
        $body = $this->createBody($subject,$componentTitle,$description,$severity);

        $this->sendMailNow($toEmail, $fromEmail, $fromName, $subject, $body, $attachments);
    }

    public function getSummaryInfo()
    {
        $locationInfo = array();
        $locationInfo['domain'] = Mage::helper('M2ePro/Client')->getDomain();
        $locationInfo['ip'] = Mage::helper('M2ePro/Client')->getIp();
        $locationInfo['directory'] = Mage::helper('M2ePro/Client')->getBaseDirectory();

        $platformInfo = array();
        $platformInfo['name'] = Mage::helper('M2ePro/Magento')->getName();
        $platformInfo['edition'] = Mage::helper('M2ePro/Magento')->getEditionName();
        $platformInfo['version'] = Mage::helper('M2ePro/Magento')->getVersion();
        $platformInfo['revision'] = Mage::helper('M2ePro/Magento')->getRevision();

        $moduleInfo = array();
        $moduleInfo['name'] = Mage::helper('M2ePro/Module')->getName();
        $moduleInfo['version'] = Mage::helper('M2ePro/Module')->getVersion();
        $moduleInfo['revision'] = Mage::helper('M2ePro/Module')->getRevision();

        $phpInfo = Mage::helper('M2ePro/Client')->getPhpSettings();
        $phpInfo['api'] = Mage::helper('M2ePro/Client')->getPhpApiName();
        $phpInfo['version'] = Mage::helper('M2ePro/Client')->getPhpVersion();
        $phpInfo['ini_file_location'] = Mage::helper('M2ePro/Client')->getPhpIniFileLoaded();

        $mysqlInfo = Mage::Helper('M2ePro/Client')->getMysqlSettings();
        $mysqlInfo['api'] = Mage::helper('M2ePro/Client')->getMysqlApiName();
        $prefix = Mage::helper('M2ePro/Magento')->getDatabaseTablesPrefix();
        $mysqlInfo['prefix'] = $prefix != '' ? $prefix : 'Disabled';
        $mysqlInfo['version'] = Mage::helper('M2ePro/Client')->getMysqlVersion();
        $mysqlInfo['database'] = Mage::helper('M2ePro/Magento')->getDatabaseName();

        $additionalInfo = array();
        $additionalInfo['system'] = Mage::helper('M2ePro/Client')->getSystem();
        $additionalInfo['user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A';
        $additionalInfo['admin'] = Mage::helper('M2ePro/View')->isBaseControllerLoaded()
                                        ? Mage::helper('adminhtml')->getUrl('adminhtml')
                                        : 'N/A';
        $additionalInfo['license_key'] = Mage::helper('M2ePro/Module_License')->getKey();
        $additionalInfo['installation_key'] = Mage::helper('M2ePro/Module')->getInstallationKey();

        $info = <<<DATA
-------------------------------- PLATFORM INFO -----------------------------------
Name: {$platformInfo['name']}
Edition: {$platformInfo['edition']}
Version: {$platformInfo['version']}
Revision: {$platformInfo['revision']}

-------------------------------- MODULE INFO -------------------------------------
Name: {$moduleInfo['name']}
Version: {$moduleInfo['version']}
Revision: {$moduleInfo['revision']}

-------------------------------- LOCATION INFO -----------------------------------
Domain: {$locationInfo['domain']}
Ip: {$locationInfo['ip']}
Directory: {$locationInfo['directory']}

-------------------------------- PHP INFO ----------------------------------------
Version: {$phpInfo['version']}
Api: {$phpInfo['api']}
Memory Limit: {$phpInfo['memory_limit']}
Max Execution Time: {$phpInfo['max_execution_time']}
PHP ini file: {$phpInfo['ini_file_location']}

-------------------------------- MYSQL INFO --------------------------------------
Version: {$mysqlInfo['version']}
Api: {$mysqlInfo['api']}
Database: {$mysqlInfo['database']}
Tables Prefix: {$mysqlInfo['prefix']}
Connection Timeout: {$mysqlInfo['connect_timeout']}
Wait Timeout: {$mysqlInfo['wait_timeout']}

------------------------------ ADDITIONAL INFO -----------------------------------
System Name: {$additionalInfo['system']}
User Agent: {$additionalInfo['user_agent']}
License Key: {$additionalInfo['license_key']}
Installation Key: {$additionalInfo['installation_key']}
Admin Panel: {$additionalInfo['admin']}
DATA;

        return $info;
    }

    //########################################

    private function createBody($subject, $component, $description, $severity)
    {
        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();

        $body = <<<DATA

{$description}

-------------------------------- GENERAL -----------------------------------------
Date: {$currentDate}
Component: {$component}
Subject: {$subject}
%severity%


DATA;

        $severity = $severity ? "Severity: {$severity}" : '';
        $body = str_replace('%severity%', $severity, $body);

        $body .= $this->getSummaryInfo();

        return $body;
    }

    private function sendMailNow($toEmail, $fromEmail, $fromName, $subject, $body, array $attachments = array())
    {
        $mail = new Zend_Mail('UTF-8');

        $mail->addTo($toEmail)
             ->setFrom($fromEmail, $fromName)
             ->setSubject($subject)
             ->setBodyText($body, null, Zend_Mime::ENCODING_8BIT);

        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment);
        }

        $mail->send();
    }

    //########################################
}