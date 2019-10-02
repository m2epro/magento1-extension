<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Processing_Runner
{
    const MAX_LIFETIME = 86400;

    /** @var Ess_M2ePro_Model_Processing $_processingObject */
    protected $_processingObject = null;

    protected $_params = array();

    //####################################

    public function setProcessingObject(Ess_M2ePro_Model_Processing $processingObject)
    {
        $this->_processingObject = $processingObject;
        $this->setParams($processingObject->getParams());

        return $this;
    }

    public function getProcessingObject()
    {
        return $this->_processingObject;
    }

    public function setParams(array $params)
    {
        $this->_params = $params;
        return $this;
    }

    public function getParams()
    {
        return $this->_params;
    }

    abstract public function getType();

    //####################################

    public function start()
    {
        $this->setProcessingObject($this->buildProcessingObject());

        $this->eventBefore();
        $this->setLocks();
    }

    abstract public function processSuccess();

    abstract public function processExpired();

    public function complete()
    {
        $this->unsetLocks();
        $this->eventAfter();

        $this->getProcessingObject()->deleteInstance();
    }

    //####################################

    protected function eventBefore()
    {
        return null;
    }

    protected function setLocks()
    {
        return null;
    }

    protected function unsetLocks()
    {
        return null;
    }

    protected function eventAfter()
    {
        return null;
    }

    //####################################

    protected function buildProcessingObject()
    {
        $processingObject = Mage::getModel('M2ePro/Processing');

        $modelName = preg_replace('/^Ess_M2ePro_Model_/', 'M2ePro/', get_class($this));

        $processingObject->setData('model', $modelName);
        $processingObject->setData('type', $this->getType());
        $processingObject->setSettings('params', $this->getParams());

        $processingObject->setData(
            'expiration_date', Mage::helper('M2ePro')->getDate(
                Mage::helper('M2ePro')->getCurrentGmtDate(true)+static::MAX_LIFETIME
            )
        );

        $processingObject->save();

        return $processingObject;
    }

    //####################################

    protected function getExpiredErrorMessage()
    {
        return Mage::helper('M2ePro')->__('Request wait timeout exceeded.');
    }

    //####################################
}