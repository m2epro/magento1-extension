<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Issue_Object as Issue;

class Ess_M2ePro_Model_Amazon_Repricing_Issue_InvalidToken extends Ess_M2ePro_Model_Issue_Locator_Abstract
{
    /** @var string */
    const CACHE_KEY = 'repricing_invalid_token_errors';
    /** @var int */
    const CACHE_LIFE = 86400;

    public function getIssues()
    {
        if (!$this->isNeedProcess()) {
            return array();
        }

        /** @var Ess_M2ePro_Helper_Data_Cache_Permanent $cache */
        $cache = Mage::helper('M2ePro/Data_Cache_Permanent');

        $cachedMessages = $cache->getValue(self::CACHE_KEY);
        if ($cachedMessages !== false) {
            return $this->makeErrorObject($cachedMessages);
        }

        /** @var Ess_M2ePro_Model_Resource_Amazon_Account_Repricing_Collection $repricingCollection */
        $repricingCollection = Mage::getModel('M2ePro/Resource_Amazon_Account_Repricing_Collection');
        $invalidAccounts = $repricingCollection->getInvalidAccounts();

        $messages = array();
        foreach ($invalidAccounts as $invalidAccount) {
            $messages[] = array(
                'title' => (string)$invalidAccount->getAccountId(),
                'text' => Mage::helper('M2ePro')->__("Your Repricer account was deleted.
            Unlink it from M2E Pro or <a href='mailto:support@m2ecloud.com'>contact support</a>
            if your Repricer account is valid.")
            );
        }

        $cache->setValue(
            self::CACHE_KEY,
            $messages,
            array('amazon', 'repricing'),
            self::CACHE_LIFE
        );

        return $this->makeErrorObject($messages);
    }

    private function makeErrorObject(array $messages)
    {
        $issues = array();
        foreach ($messages as $message) {
            $issues[] = Mage::getModel(
                'M2ePro/Issue_Object',
                array(
                    Issue::KEY_TYPE => Mage_Core_Model_Message::ERROR,
                    Issue::KEY_TITLE => isset($message['title']) ? $message['title'] : '',
                    Issue::KEY_TEXT => isset($message['text']) ? $message['text'] : '',
                    Issue::KEY_URL => null,
                )
            );
        }

        return $issues;
    }

    /**
     * @return bool
     */
    private function isNeedProcess()
    {
        if (!Mage::helper('M2ePro/Component_Amazon')->isEnabled()) {
            return false;
        }

        if (!Mage::helper('M2ePro/View_Amazon')->isInstallationWizardFinished()) {
            return false;
        }

        return true;
    }
}
