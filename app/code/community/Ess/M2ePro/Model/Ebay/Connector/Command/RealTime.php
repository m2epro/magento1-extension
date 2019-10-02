<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Connector_Command_RealTime extends Ess_M2ePro_Model_Connector_Command_RealTime
{
    /**
     * @var Ess_M2ePro_Model_Marketplace|null
     */
    protected $_marketplace = null;

    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $_account = null;

    // ########################################

    public function __construct(
        array $params = array(),
        Ess_M2ePro_Model_Marketplace $marketplace = null,
        Ess_M2ePro_Model_Account $account = null
    ) {
        $this->_marketplace = $marketplace;
        $this->_account     = $account;

        parent::__construct($params);
    }

    // ########################################

    protected function buildRequestInstance()
    {
        $request = parent::buildRequestInstance();

        $requestData = $request->getData();

        if ($this->_marketplace !== null) {
            $requestData['marketplace'] = $this->_marketplace->getNativeId();
        }

        if ($this->_account !== null) {
            $requestData['account'] = $this->_account->getChildObject()->getServerHash();
        }

        $request->setData($requestData);

        return $request;
    }

    // ########################################
}
