<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Response_Message_Set
{
    /** @var Ess_M2ePro_Model_Response_Message[] $_entities */
    protected $_entities = array();

    //########################################

    public function init(array $responseData)
    {
        $this->clearEntities();

        foreach ($responseData as $messageData) {
            $message = $this->getEntityModel();
            $message->initFromResponseData($messageData);

            $this->_entities[] = $message;
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
        $this->_entities[] = $message;
    }

    public function clearEntities()
    {
        $this->_entities = array();
    }

    //########################################

    public function getEntities()
    {
        return $this->_entities;
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

    //########################################

    public function hasErrorEntities()
    {
        $errors = $this->getErrorEntities();
        return !empty($errors);
    }

    public function hasWarningEntities()
    {
        $errors = $this->getWarningEntities();
        return !empty($errors);
    }

    public function hasSuccessEntities()
    {
        $errors = $this->getSuccessEntities();
        return !empty($errors);
    }

    public function hasNoticeEntities()
    {
        $errors = $this->getNoticeEntities();
        return !empty($errors);
    }

    //########################################

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