<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Issue_Object extends Varien_Object
{
    const KEY_TITLE = 'title';
    const KEY_TEXT  = 'text';
    const KEY_TYPE  = 'type';
    const KEY_URL   = 'url';

    //########################################

    public function getTitle()
    {
        return $this->getData(self::KEY_TITLE);
    }

    public function getText()
    {
        return $this->getData(self::KEY_TEXT);
    }

    public function getType()
    {
        return $this->getData(self::KEY_TYPE);
    }

    // ---------------------------------------

    public function getUrl()
    {
        return $this->getData(self::KEY_URL);
    }

    //########################################

    public function isNotice()
    {
        return $this->getType() === Mage_Core_Model_Message::NOTICE;
    }

    public function isSuccess()
    {
        return $this->getType() === Mage_Core_Model_Message::SUCCESS;
    }

    public function isError()
    {
        return $this->getType() === Mage_Core_Model_Message::ERROR;
    }

    public function isWarning()
    {
        return $this->getType() === Mage_Core_Model_Message::WARNING;
    }

    //########################################
}
