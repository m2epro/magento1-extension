<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Translation_Connector_RealTime extends Ess_M2ePro_Model_Connector_Command_RealTime
{
    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $_account;

    //########################################

    public function __construct(
        array $params = array(),
        Ess_M2ePro_Model_Account $account = null
    ) {
        $this->_account = $account;
        parent::__construct($params);
    }

    //########################################

    protected function buildRequestInstance()
    {
        $request = parent::buildRequestInstance();

        $requestData = $request->getData();
        if ($this->_account !== null) {
            $requestData['account'] = $this->_account->getChildObject()->getTranslationHash();
        }

        $request->setData($requestData);

        return $request;
    }

    //########################################
}
