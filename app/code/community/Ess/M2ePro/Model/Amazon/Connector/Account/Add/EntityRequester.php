<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Account_Add_EntityRequester
    extends Ess_M2ePro_Model_Amazon_Connector_Command_RealTime
{
    /**
     * @return array
     */
    public function getRequestData()
    {
        return array(
            'merchant_id' => $this->_params['merchant_id'],
            'oauth_code' => $this->_params['oauth_code'],
            'marketplace_id' => $this->_params['marketplace_id'],
        );
    }

    /**
     * @return array
     */
    protected function getCommand()
    {
        return array('account', 'add', 'entity');
    }

    //########################################

    /**
     * @return bool
     */
    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();
        if ((empty($responseData['hash']) || !isset($responseData['info'])) &&
            !$this->getResponse()->getMessages()->hasErrorEntities()
        ) {
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    protected function prepareResponseData()
    {
        foreach ($this->getResponse()->getMessages()->getEntities() as $message) {
            if (!$message->isError()) {
                continue;
            }

            throw new Exception($message->getText());
        }

        $this->_responseData = $this->getResponse()->getData();
    }

    //########################################
}
