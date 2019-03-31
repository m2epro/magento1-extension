<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Connector_Command_Pending_Requester
    extends Ess_M2ePro_Model_Connector_Command_Pending_Requester
{
    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    protected $marketplace = NULL;

    /**
     * @var Ess_M2ePro_Model_Account
     */
    protected $account = NULL;

    // ########################################

    public function __construct(array $params = array(),
                                Ess_M2ePro_Model_Marketplace $marketplace = NULL,
                                Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->marketplace = $marketplace;
        $this->account     = $account;

        parent::__construct($params);
    }

    // ########################################

    protected function buildRequestInstance()
    {
        $request = parent::buildRequestInstance();

        $requestData = $request->getData();

        if (!is_null($this->marketplace)) {
            $requestData['marketplace'] = $this->marketplace->getNativeId();
        }
        if (!is_null($this->account)) {
            $requestData['account'] = $this->account->getChildObject()->getServerHash();
        }

        $request->setData($requestData);

        return $request;
    }

    // ########################################

    protected function getProcessingParams()
    {
        $params = parent::getProcessingParams();

        if (!is_null($this->marketplace)) {
            $params['marketplace_id'] = $this->marketplace->getId();
        }
        if (!is_null($this->account)) {
            $params['account_id'] = $this->account->getId();
        }

        return $params;
    }

    protected function getResponserParams()
    {
        $params = parent::getResponserParams();

        if (!is_null($this->marketplace)) {
            $params['marketplace_id'] = $this->marketplace->getId();
        }
        if (!is_null($this->account)) {
            $params['account_id'] = $this->account->getId();
        }

        return $params;
    }

    // ########################################
}