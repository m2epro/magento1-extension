<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Translation_Requester extends Ess_M2ePro_Model_Connector_Requester
{
    const COMPONENT = 'Translation';
    const COMPONENT_VERSION = 1;

    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $account = NULL;

    // ########################################

    public function __construct(array $params = array(),
                                Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->account = $account;
        parent::__construct($params);
    }

    // ########################################

    protected function getComponent()
    {
        return self::COMPONENT;
    }

    protected function getComponentVersion()
    {
        return self::COMPONENT_VERSION;
    }

    // ########################################

    public function process()
    {
        if (!is_null($this->account)) {
            $this->requestExtraData['account'] = $this->account->getChildObject()->getTranslationHash();
        }

        return parent::process();
    }

    // ########################################
}
