<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Requirements_Checks_Abstract
{
    const NICK = 'Abstract';

    /** @var Ess_M2ePro_Model_Requirements_Semver_VersionParser */
    protected $_versionParser;

    //########################################

    abstract public function isMeet();
    abstract public function getMin();
    abstract public function getReal();

    //########################################

    /**
     * @return Ess_M2ePro_Model_Requirements_Renderer_Abstract
     */
    public function getRenderer()
    {
        $model = Mage::getModel('M2ePro/Requirements_Renderer_' . static::NICK, array($this));
        return $model;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Requirements_Reader
     */
    public function getReader()
    {
        return Mage::getSingleton('M2ePro/Requirements_Reader');
    }

    public function getVersionParser()
    {
        if ($this->_versionParser === null) {
            $this->_versionParser = new Ess_M2ePro_Model_Requirements_Semver_VersionParser();
        }

        return $this->_versionParser;
    }

    //########################################
}
