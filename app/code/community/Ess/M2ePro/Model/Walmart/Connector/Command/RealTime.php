<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Walmart_Connector_Command_RealTime extends Ess_M2ePro_Model_Connector_Command_RealTime
{
    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $account = NULL;

    //########################################

    public function __construct(array $params = array(),
                                Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->account = $account;
        parent::__construct($params);
    }

    //########################################

    protected function buildRequestInstance()
    {
        $request = parent::buildRequestInstance();

        $requestData = $request->getData();
        if (!is_null($this->account)) {
            $requestData['account'] = $this->account->getChildObject()->getServerHash();
        }
        $request->setData($requestData);

        return $request;
    }

    //########################################
}