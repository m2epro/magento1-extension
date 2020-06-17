<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Exception_Connection extends Ess_M2ePro_Model_Exception
{
    const CONNECTION_ERROR_REPEAT_TIMEOUT = 180;

    //########################################

    public function __construct($message, $additionalData = array())
    {
        parent::__construct($message, $additionalData, 0, false);
    }

    //########################################

    /**
     * @param string $key
     *
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function handleRepeatTimeout($key)
    {
        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();

        $firstConnectionErrorDate = $this->getFirstConnectionErrorDate($key);
        if (empty($firstConnectionErrorDate)) {
            $this->setFirstConnectionErrorDate($key, $currentDate);

            return true;
        }

        $currentDateTimeStamp = strtotime($currentDate);
        $errorDateTimeStamp   = strtotime($firstConnectionErrorDate);
        if ($currentDateTimeStamp - $errorDateTimeStamp < self::CONNECTION_ERROR_REPEAT_TIMEOUT) {
            return true;
        }

        if (!empty($firstConnectionErrorDate)) {
            $this->removeFirstConnectionErrorDate($key);
        }

        return false;
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    protected function getFirstConnectionErrorDate($key)
    {
        return Mage::getModel('M2ePro/Registry')->loadByKey($key)->getValue();
    }

    /**
     * @param string $key
     * @param string $date
     *
     */
    protected function setFirstConnectionErrorDate($key, $date)
    {
        Mage::getModel('M2ePro/Registry')
            ->loadByKey($key)
            ->setValue($date)
            ->save();
    }

    /**
     * @param string $key
     *
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function removeFirstConnectionErrorDate($key)
    {
        Mage::getModel('M2ePro/Registry')
            ->loadByKey($key)
            ->delete();
    }

    //########################################
}
