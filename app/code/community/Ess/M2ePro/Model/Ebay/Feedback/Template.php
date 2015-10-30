<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Feedback_Template extends Ess_M2ePro_Model_Component_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Account
     */
    private $accountModel = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Feedback_Template');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->accountModel = NULL;
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if (is_null($this->accountModel)) {
            $this->accountModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Account',$this->getData('account_id')
            );
        }

        return $this->accountModel;
    }

    /**
     * @param Ess_M2ePro_Model_Account $instance
     */
    public function setAccount(Ess_M2ePro_Model_Account $instance)
    {
        $this->accountModel = $instance;
    }

    //########################################

    public function getBody()
    {
        return $this->getData('body');
    }

    //########################################
}