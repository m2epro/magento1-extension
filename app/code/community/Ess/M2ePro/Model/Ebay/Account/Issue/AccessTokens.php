<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Issue_Object as Issue;

class Ess_M2ePro_Model_Ebay_Account_Issue_AccessTokens extends Ess_M2ePro_Model_Issue_Locator_Abstract
{
    const CACHE_KEY = __CLASS__;

    //########################################

    public function getIssues()
    {
        if (!$this->isNeedProcess()) {
            return array();
        }

        $messagesData = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue(self::CACHE_KEY);
        if ($messagesData === false) {
            /** @var $accounts Ess_M2ePro_Model_Resource_Account_Collection */
            $accounts = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');

            $messagesData = array();
            foreach ($accounts->getItems() as $account) {
                /** @var Ess_M2ePro_Model_Account $account */
                $messagesData = array_merge(
                    $messagesData,
                    $this->getSellApiTokenMessages($account)
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

    //########################################


    protected function getSellApiTokenMessages(Ess_M2ePro_Model_Account $account)
    {
        $expirationDate =  $account->getChildObject()->getSellApiTokenExpiredDate();
        if (empty($expirationDate) || !$account->getChildObject()->isTokenExist()) {
            return array();
        }

        /** @var Ess_M2ePro_Helper_Data $helper */
        $helper = Mage::helper('M2ePro');
        $currentTimeStamp = $helper->getCurrentGmtDate(true);
        $tokenExpirationTimeStamp = (int)$helper->createGmtDateTime($expirationDate)->format('U');

        if ($tokenExpirationTimeStamp <= 0) { // if value in db = 0000-00-00
            return array();
        }

        if ($tokenExpirationTimeStamp < $currentTimeStamp) {
            $tempMessage = Mage::helper('M2ePro')->__(
                <<<TEXT
Attention! The Sell API token for <a href="%url%" target="_blank">"%name%"</a> eBay account has expired.
You need to generate a new access token to reauthorize M2E Pro.
TEXT
                ,
                Mage::helper('adminhtml')->getUrl(
                    'M2ePro/adminhtml_ebay_account/edit', array('id' => $account->getId())
                ),
                Mage::helper('M2ePro')->escapeHtml($account->getTitle())
            );

            $editHash = sha1(
                self::CACHE_KEY.$account->getId().$tokenExpirationTimeStamp.
                Mage_Core_Model_Message::ERROR.__METHOD__
            );

            return array(array(
                Issue::KEY_TYPE  => Mage_Core_Model_Message::ERROR,
                Issue::KEY_TITLE => Mage::helper('M2ePro')->__(
                    'Attention! The Sell API token for "%name%" eBay account has expired.
                    You need to generate a new access token to reauthorize M2E Pro.',
                    Mage::helper('M2ePro')->escapeHtml($account->getTitle())
                ),
                Issue::KEY_TEXT  => $tempMessage,
                Issue::KEY_URL   => Mage::helper('M2ePro/Module_Support')->getSupportUrl('/support/solutions/articles/9000219023') .'/?'.
                                    $editHash
            ));
        }

        if (($currentTimeStamp + 60*60*24*10) >= $tokenExpirationTimeStamp) {
            $tempMessage = Mage::helper('M2ePro')->__(
                <<<TEXT
Attention! The Sell API token for <a href="%url%" target="_blank">"%name%"</a> eBay Account expires on %date%.
You need to generate a new access token to reauthorize M2E Pro.
TEXT
                ,
                Mage::helper('adminhtml')->getUrl(
                    'M2ePro/adminhtml_ebay_account/edit', array('id' => $account->getId())
                ),
                Mage::helper('M2ePro')->escapeHtml($account->getTitle()),
                Mage::app()->getLocale()
                    ->date($tokenExpirationTimeStamp)
                    ->toString(
                        Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM)
                    )
            );

            $editHash = sha1(
                self::CACHE_KEY.$account->getId().$tokenExpirationTimeStamp.
                Mage_Core_Model_Message::NOTICE.__METHOD__
            );

            return array(array(
                Issue::KEY_TYPE  => Mage_Core_Model_Message::NOTICE,
                Issue::KEY_TITLE => Mage::helper('M2ePro')->__(
                    'Attention! The Sell API token for "%name%" eBay account is to expire.
                    You need to generate a new access token to reauthorize M2E Pro.',
                    Mage::helper('M2ePro')->escapeHtml($account->getTitle())
                ),
                Issue::KEY_TEXT  => $tempMessage,
                Issue::KEY_URL   => Mage::helper('M2ePro/Module_Support')->getSupportUrl('/support/solutions/articles/9000219023') .'/?'.
                                    $editHash
            ));
        }

        return array();
    }

    //########################################

    public function isNeedProcess()
    {
        return Mage::helper('M2ePro/View_Ebay')->isInstallationWizardFinished() &&
               Mage::helper('M2ePro/Component_Ebay')->isEnabled();
    }

    //########################################
}
