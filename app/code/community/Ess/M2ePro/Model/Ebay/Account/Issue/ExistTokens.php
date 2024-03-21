<?php

use Ess_M2ePro_Model_Issue_Object as Issue;

class Ess_M2ePro_Model_Ebay_Account_Issue_ExistTokens extends Ess_M2ePro_Model_Issue_Locator_Abstract
{
    const CACHE_KEY = __CLASS__;

    public function getIssues()
    {
        if (!$this->isNeedProcess()) {
            return array();
        }

        $messagesData = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue(self::CACHE_KEY);
        if ($messagesData === false) {
            /** @var $accounts Ess_M2ePro_Model_Resource_Account_Collection */
            $accounts = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
            $accounts->addFieldToFilter('is_token_exist', array('eq' => 0));

            $messagesData = array();
            foreach ($accounts->getItems() as $account) {
                /** @var Ess_M2ePro_Model_Account $account */
                $messagesData = array_merge(
                    $messagesData,
                    $this->getExistTokenMessages($account)
                );
            }

            Mage::helper('M2ePro/Data_Cache_Permanent')->setValue(
                self::CACHE_KEY, $messagesData, array('account','ebay'), 60*60*24
            );
        }

        $issues = array();
        foreach ($messagesData as $messageData) {
            $issues[] = Mage::getModel('M2ePro/Issue_Object', $messageData);
        }

        return $issues;
    }


    private function isNeedProcess()
    {
        return Mage::helper('M2ePro/View_Ebay')->isInstallationWizardFinished()
            && Mage::helper('M2ePro/Component_Ebay')->isEnabled();
    }

    private function getExistTokenMessages(Ess_M2ePro_Model_Account $account)
    {
        $tempMessage = Mage::helper('M2ePro')->__(
            <<<TEXT
'Authorization for "%title%" eBay account failed. Please go to eBay > Configuration >
            Accounts > "%title%" eBay Account > General and click Get Token to renew it.'
TEXT
            ,
            Mage::helper('M2ePro')->escapeHtml($account->getTitle())
        );

        return array(
            array(
            Issue::KEY_TYPE  => Mage_Core_Model_Message::ERROR,
            Issue::KEY_TITLE => Mage::helper('M2ePro')->__('Authorization failed'),
            Issue::KEY_TEXT  => $tempMessage
            )
        );
    }
}