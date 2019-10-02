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
            $accounts->addFieldToFilter('token_session', array('notnull' => true));

            $messagesData = array();
            foreach ($accounts->getItems() as $account) {
                /** @var Ess_M2ePro_Model_Account $account */
                $messagesData = array_merge(
                    $messagesData,
                    $this->getTradingApiTokenMessages($account),
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

    protected function getTradingApiTokenMessages(Ess_M2ePro_Model_Account $account)
    {
        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentTimezoneDate(true);
        $tokenExpirationTimeStamp = strtotime($account->getChildObject()->getTokenExpiredDate());

        if ($tokenExpirationTimeStamp < $currentTimeStamp) {
            $textToTranslate = <<<TEXT
Attention! The API token for "%account_title%" eBay Account is expired. The inventory and order synchronization
with eBay marketplace cannot be maintained until you grant M2E Pro the access token.<br>

Please, go to <i>%menu_label% > Configuration > eBay Account > <a href="%url%" target="_blank">General TAB</a></i>,
click Get Token. After you are redirected to the eBay website, sign into your seller account,
then click Agree to generate a new access token.<br>

<b>Note:</b> After the new eBay token is obtained, click <b>Save</b> to apply the changes to your
Account Configuration.
TEXT;
            if ($account->getChildObject()->getSellApiTokenSession()) {
                $textToTranslate = <<<TEXT
Attention! The Trading API token for "%account_title%" eBay Account is expired. The inventory and order synchronization
with eBay marketplace cannot be maintained until you grant M2E Pro the access token.<br>

Please go to <i>%menu_label% > Configuration > Accounts > eBay Account > General >&nbsp
<a href="%url%" target="_blank">Trading API Details</a></i> and click Get Token. After you are redirected to the
eBay website, sign into your seller account, then click Agree to generate a new access token.<br>

<b>Note:</b> After the new eBay token is obtained, click <b>Save</b> to apply the changes to your
Account Configuration.
TEXT;
            }

            $tempTitle = Mage::helper('M2ePro')->__(
                'Attention! M2E Pro needs to be reauthorized: the API token for "%account_title%" eBay Account is
                expired. Please generate a new access token.',
                Mage::helper('M2ePro')->escapeHtml($account->getTitle())
            );
            $tempMessage = Mage::helper('M2ePro')->__(
                $textToTranslate,
                Mage::helper('M2ePro')->escapeHtml($account->getTitle()),
                Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel(),
                Mage::helper('adminhtml')->getUrl(
                    'M2ePro/adminhtml_ebay_account/edit', array('id' => $account->getId())
                )
            );

            $editHash = sha1(
                self::CACHE_KEY.$account->getId().$tokenExpirationTimeStamp.
                Mage_Core_Model_Message::ERROR.__METHOD__
            );

            $messageUrl = Mage::helper('adminhtml')->getUrl(
                'M2ePro/adminhtml_ebay_account/edit',
                array('id' => $account->getId(), '_query' => array('hash' => $editHash))
            );

            return array(array(
                Issue::KEY_TYPE  => Mage_Core_Model_Message::ERROR,
                Issue::KEY_TITLE => $tempTitle,
                Issue::KEY_TEXT  => $tempMessage,
                Issue::KEY_URL   => $messageUrl
            ));
        }

        if (($currentTimeStamp + 60*60*24*10) >= $tokenExpirationTimeStamp) {
            $textToTranslate = <<<TEXT
Attention! The API token for "%account_title%" eBay Account expires on %date%.
It needs to be renewed to maintain the inventory and order synchronization with eBay marketplace.<br>

Please, go to <i>%menu_label% > Configuration > eBay Account > <a href="%url%" target="_blank">General TAB</a></i>,
click Get Token. After you are redirected to the eBay website, sign into your seller account,
then click Agree to generate a new access token.<br>

<b>Note:</b> After the new eBay token is obtained, click <b>Save</b> to apply the changes to your
Account Configuration.
TEXT;
            if ($account->getChildObject()->getSellApiTokenSession()) {
                $textToTranslate = <<<TEXT
Attention! The Trading API token for "%account_title%" eBay Account expires on %date%.
It needs to be renewed to maintain the inventory and order synchronization with eBay marketplace.<br>

Please go to <i>%menu_label% > Configuration > Accounts > eBay Account > General >&nbsp
<a href="%url%" target="_blank">Trading API Details</a></i> and click Get Token. After you are redirected to the
eBay website, sign into your seller account, then click Agree to generate a new access token.<br>

<b>Note:</b> After the new eBay token is obtained, click <b>Save</b> to apply the changes to your
Account Configuration.
TEXT;
            }

            $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
            $tempTitle = Mage::helper('M2ePro')->__(
                'Attention! M2E Pro needs to be reauthorized: the API token for "%account_title%" eBay Account
                is to expire. Please generate a new access token.',
                Mage::helper('M2ePro')->escapeHtml($account->getTitle())
            );

            $tempMessage = Mage::helper('M2ePro')->__(
                $textToTranslate,
                Mage::helper('M2ePro')->escapeHtml($account->getTitle()),
                Mage::app()->getLocale()->date($tokenExpirationTimeStamp)->toString($format),
                Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel(),
                Mage::helper('adminhtml')->getUrl(
                    'M2ePro/adminhtml_ebay_account/edit', array('id' => $account->getId())
                )
            );

            $editHash = sha1(
                self::CACHE_KEY.$account->getId().$tokenExpirationTimeStamp.
                Mage_Core_Model_Message::NOTICE.__METHOD__
            );

            $messageUrl = Mage::helper('adminhtml')->getUrl(
                'M2ePro/adminhtml_ebay_account/edit',
                array('id' => $account->getId(), '_query' => array('hash' => $editHash))
            );

            return array(array(
                Issue::KEY_TYPE  => Mage_Core_Model_Message::NOTICE,
                Issue::KEY_TITLE => $tempTitle,
                Issue::KEY_TEXT  => $tempMessage,
                Issue::KEY_URL   => $messageUrl
            ));
        }

        return array();
    }

    protected function getSellApiTokenMessages(Ess_M2ePro_Model_Account $account)
    {
        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentTimezoneDate(true);
        $tokenExpirationTimeStamp = strtotime($account->getChildObject()->getSellApiTokenExpiredDate());

        if ($tokenExpirationTimeStamp <= 0) {
            return array();
        }

        if ($tokenExpirationTimeStamp < $currentTimeStamp) {
            $textToTranslate = <<<TEXT
Attention! The Sell API token for "%account_title%" eBay Account is expired. The inventory and order synchronization
with eBay marketplace cannot be maintained until you grant M2E Pro the access token.<br>

Please go to <i>%menu_label% > Configuration > Accounts > eBay Account > General >&nbsp
<a href="%url%" target="_blank">Sell API Details</a></i> and click Get Token. After you are redirected to the
eBay website, sign into your seller account, then click Agree to generate a new access token.<br>

<b>Note:</b> After the new eBay token is obtained, click <b>Save</b> to apply the changes to your
Account Configuration.
TEXT;

            $tempTitle = Mage::helper('M2ePro')->__(
                'Attention! M2E Pro needs to be reauthorized: the API token for "%account_title%" eBay Account is
                expired. Please generate a new access token.',
                Mage::helper('M2ePro')->escapeHtml($account->getTitle())
            );
            $tempMessage = Mage::helper('M2ePro')->__(
                $textToTranslate,
                Mage::helper('M2ePro')->escapeHtml($account->getTitle()),
                Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel(),
                Mage::helper('adminhtml')->getUrl(
                    'M2ePro/adminhtml_ebay_account/edit', array('id' => $account->getId())
                )
            );

            $editHash = sha1(
                self::CACHE_KEY.$account->getId().$tokenExpirationTimeStamp.
                Mage_Core_Model_Message::ERROR.__METHOD__
            );

            $messageUrl = Mage::helper('adminhtml')->getUrl(
                'M2ePro/adminhtml_ebay_account/edit',
                array('id' => $account->getId(), '_query' => array('hash' => $editHash))
            );

            return array(array(
                Issue::KEY_TYPE  => Mage_Core_Model_Message::ERROR,
                Issue::KEY_TITLE => $tempTitle,
                Issue::KEY_TEXT  => $tempMessage,
                Issue::KEY_URL   => $messageUrl
            ));
        }

        if (($currentTimeStamp + 60*60*24*10) >= $tokenExpirationTimeStamp) {
            $textToTranslate = <<<TEXT
Attention! The Sell API token for "%account_title%" eBay Account expires on %date%.
It needs to be renewed to maintain the inventory and order synchronization with eBay marketplace.<br>

Please go to <i>%menu_label% > Configuration > Accounts > eBay Account > General >&nbsp
<a href="%url%" target="_blank">Sell API Details</a></i> and click Get Token. After you are redirected to the
eBay website, sign into your seller account, then click Agree to generate a new access token.<br>

<b>Note:</b> After the new eBay token is obtained, click <b>Save</b> to apply the changes to your
Account Configuration.
TEXT;
            $tempTitle = Mage::helper('M2ePro')->__(
                'Attention! M2E Pro needs to be reauthorized: the API token for "%account_title%" eBay Account
                is to expire. Please generate a new access token.',
                Mage::helper('M2ePro')->escapeHtml($account->getTitle())
            );

            $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
            $tempMessage = Mage::helper('M2ePro')->__(
                $textToTranslate,
                Mage::helper('M2ePro')->escapeHtml($account->getTitle()),
                Mage::app()->getLocale()->date($tokenExpirationTimeStamp)->toString($format),
                Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel(),
                Mage::helper('adminhtml')->getUrl(
                    'M2ePro/adminhtml_ebay_account/edit', array('id' => $account->getId())
                )
            );

            $editHash = sha1(
                self::CACHE_KEY.$account->getId().$tokenExpirationTimeStamp.
                Mage_Core_Model_Message::NOTICE.__METHOD__
            );

            $messageUrl = Mage::helper('adminhtml')->getUrl(
                'M2ePro/adminhtml_ebay_account/edit',
                array('id' => $account->getId(), '_query' => array('hash' => $editHash))
            );

            return array(array(
                Issue::KEY_TYPE  => Mage_Core_Model_Message::NOTICE,
                Issue::KEY_TITLE => $tempTitle,
                Issue::KEY_TEXT  => $tempMessage,
                Issue::KEY_URL   => $messageUrl
            ));
        }

        return array();
    }

    //########################################

    public function isNeedProcess()
    {
        return Mage::helper('M2ePro/View_Ebay')->isInstallationWizardFinished() &&
               Mage::helper('M2ePro/Component_Ebay')->isActive();
    }

    //########################################
}
