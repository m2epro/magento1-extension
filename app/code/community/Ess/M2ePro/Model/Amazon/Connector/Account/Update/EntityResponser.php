<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Account_Update_EntityResponser
    extends Ess_M2ePro_Model_Amazon_Connector_Command_Pending_Responser
{
    //########################################

    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();
        if (!isset($responseData['info'])) {
            return false;
        }

        return true;
    }

    protected function processResponseData()
    {
        $responseData = $this->getPreparedResponseData();

        /** @var $amazonAccount Ess_M2ePro_Model_Amazon_Account */
        $amazonAccount = $this->getAccount()->getChildObject();

        $dataForUpdate = array(
            'info' => Mage::helper('M2ePro')->jsonEncode($responseData['info'])
        );

        $amazonAccount->addData($dataForUpdate)->save();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account', 'account_id');
    }

    //########################################
}
