<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Repricing_Abstract
{
    /** @var Ess_M2ePro_Model_Account $account */
    private $account = NULL;

    //########################################

    public function __construct(Ess_M2ePro_Model_Account $account)
    {
        $this->account = $account;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->account;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Account
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getAmazonAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Account_Repricing
     */
    protected function getAmazonAccountRepricing()
    {
        return $this->getAmazonAccount()->getRepricing();
    }

    /**
     * @return Ess_M2ePro_Helper_Component_Amazon_Repricing
     */
    protected function getHelper()
    {
        return Mage::helper('M2ePro/Component_Amazon_Repricing');
    }

    //########################################
}