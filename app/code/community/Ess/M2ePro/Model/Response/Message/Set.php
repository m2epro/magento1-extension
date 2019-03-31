<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Response_Message_Set
{
    /** @var Ess_M2ePro_Model_Response_Message[] $entities */
    protected $entities = array();

    //########################################

    public function init(array $responseData)
    {
        $this->clearEntities();

        foreach ($responseData as $messageData) {

            $message = $this->getEntityModel();
            $message->initFromResponseData($messageData);

            $this->entities[] = $message;
        }
    }

    /** @return Ess_M2ePro_Model_Response_Message */
    protected function getEntityModel()
    {
        return Mage::getModel('M2ePro/Response_Message');
    }

    //########################################

    public function addEntity(Ess_M2ePro_Model_Response_Message $message)
    {
        $this->entities[] = $message;
    }

    public function clearEntities()
    {
        $this->entities = array();
    }

    //########################################

    public function getEntities()
    {
        return $this->entities;
    }

    public function getEntitiesAsArrays()
    {
        $result = array();

        foreach ($this->getEntities() as $message) {
            $result[] = $message->asArray();
        }

        return $result;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Response_Message[]
     */
    public function getErrorEntities()
    {
        $messages = array();

        foreach ($this->getEntities() as $message) {
            $message->isError() && $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @return Ess_M2ePro_Model_Response_Message[]
     */
    public function getWarningEntities()
    {
        $messages = array();

        foreach ($this->getEntities() as $message) {
            $message->isWarning() && $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @return Ess_M2ePro_Model_Response_Message[]
     */
    public function getSuccessEntities()
    {
        $messages = array();

        foreach ($this->getEntities() as $message) {
            $message->isSuccess() && $messages[] = $message;
        }

        return $messages;
    }

    /**
     * @return Ess_M2ePro_Model_Response_Message[]
     */
    public function getNoticeEntities()
    {
        $messages = array();

        foreach ($this->getEntities() as $message) {
            $message->isNotice() && $messages[] = $message;
        }

        return $messages;
    }

    // ########################################

    public function hasErrorEntities()
    {
        return count($this->getErrorEntities()) > 0;
    }

    public function hasWarningEntities()
    {
        return count($this->getWarningEntities()) > 0;
    }

    public function hasSuccessEntities()
    {
        return count($this->getSuccessEntities()) > 0;
    }

    public function hasNoticeEntities()
    {
        return count($this->getNoticeEntities()) > 0;
    }

    // ########################################

    public function getCombinedErrorsString()
    {
        $messages = array();

        foreach ($this->getErrorEntities() as $message) {
            $messages[] = $message->getText();
        }

        return !empty($messages) ? implode(', ', $messages) : null;
    }

    //########################################
}