<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/** @method Ess_M2ePro_Model_Connector_Connection_Response_Message[] getErrorEntities */

class Ess_M2ePro_Model_Connector_Connection_Response_Message_Set extends Ess_M2ePro_Model_Response_Message_Set
{
    //########################################

    /** @return Ess_M2ePro_Model_Connector_Connection_Response_Message */
    protected function getEntityModel()
    {
        return Mage::getModel('M2ePro/Connector_Connection_Response_Message');
    }

    // ########################################

    public function hasSystemErrorEntity()
    {
        foreach ($this->getErrorEntities() as $message) {
            if ($message->isSenderSystem()) {
                return true;
            }
        }

        return false;
    }

    public function getCombinedSystemErrorsString()
    {
        $messages = array();

        foreach ($this->getErrorEntities() as $message) {

            if (!$message->isSenderSystem()) {
                continue;
            }

            $messages[] = $message->getText();
        }

        return !empty($messages) ? implode(', ', $messages) : null;
    }

    //########################################
}