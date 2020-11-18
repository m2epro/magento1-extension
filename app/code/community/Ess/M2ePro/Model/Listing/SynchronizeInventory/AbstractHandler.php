<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Listing_SynchronizeInventory_AbstractHandler
{
    /** @var array */
    protected $_responserParams;

    /** @var Ess_M2ePro_Model_Account */
    protected $_account;

    /** @var int */
    protected $_logsActionId;

    //########################################

    /**
     * @param array $responseData
     * @return array|void
     */
    abstract public function handle(array $responseData);

    /**
     * @return string
     */
    abstract protected function getComponentMode();

    /**
     * @return string
     */
    abstract protected function getInventoryIdentifier();

    /**
     * @param array $responserParams
     * @return $this
     */
    public function setResponserParams(array $responserParams)
    {
        $this->_responserParams = $responserParams;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        if ($this->_account !== null) {
            return $this->_account;
        }

        $upperCasedComponent = ucfirst($this->getComponentMode());

        return $this->_account = Mage::helper("M2ePro/Component_{$upperCasedComponent}")->getObject(
            'Account',
            $this->_responserParams['account_id']
        );
    }

    /**
     * @return int
     */
    protected function getLogsActionId()
    {
        if ($this->_logsActionId !== null) {
            return $this->_logsActionId;
        }

        return $this->_logsActionId = Mage::getModel('M2ePro/Listing_Log')->getResource()->getNextActionId();
    }

    //########################################
}
