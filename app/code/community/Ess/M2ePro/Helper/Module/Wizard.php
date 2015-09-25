<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Helper_Module_Wizard extends Mage_Core_Helper_Abstract
{
    const STATUS_NOT_STARTED = 0;
    const STATUS_ACTIVE      = 1;
    const STATUS_COMPLETED   = 2;
    const STATUS_SKIPPED     = 3;

    const KEY_VIEW     = 'view';
    const KEY_STATUS   = 'status';
    const KEY_STEP     = 'step';
    const KEY_PRIORITY = 'priority';
    const KEY_TYPE     = 'type';

    const TYPE_SIMPLE  = 0;
    const TYPE_BLOCKER = 1;

    private $cache = null;

    // ########################################

    /**
     * Wizards Factory
     * @param string $nick
     * @return Ess_M2ePro_Model_Wizard
     */
    public function getWizard($nick)
    {
        return Mage::getSingleton('M2ePro/Wizard_'.ucfirst($nick));
    }

    // ########################################

    public function isNotStarted($nick)
    {
        return $this->getStatus($nick) == self::STATUS_NOT_STARTED &&
               $this->getWizard($nick)->isActive();
    }

    public function isActive($nick)
    {
        return $this->getStatus($nick) == self::STATUS_ACTIVE &&
               $this->getWizard($nick)->isActive();
    }

    public function isCompleted($nick)
    {
        return $this->getStatus($nick) == self::STATUS_COMPLETED;
    }

    public function isSkipped($nick)
    {
        return $this->getStatus($nick) == self::STATUS_SKIPPED;
    }

    public function isFinished($nick)
    {
        return $this->isCompleted($nick) || $this->isSkipped($nick);
    }

    // ########################################

    private function getConfigValue($nick, $key)
    {
        Mage::helper('M2ePro/Module')->isDevelopmentEnvironment() && $this->loadCache();

        if (!is_null($this->cache)) {
            return $this->cache[$nick][$key];
        }

        if (($this->cache = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue('wizard')) !== false) {
            $this->cache = json_decode($this->cache, true);
            return $this->cache[$nick][$key];
        }

        $this->loadCache();

        return $this->cache[$nick][$key];
    }

    private function setConfigValue($nick, $key, $value)
    {
        (is_null($this->cache) || Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) && $this->loadCache();

        $this->cache[$nick][$key] = $value;

        Mage::helper('M2ePro/Data_Cache_Permanent')->setValue('wizard',
                                                    json_encode($this->cache),
                                                    array('wizard'),
                                                    60*60);

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::getSingleton('core/resource')->getTableName('m2epro_wizard');

        $connWrite->update(
            $tableName,
            array($key => $value),
            array('nick = ?' => $nick)
        );

        return $this;
    }

    // --------------------------------------------

    public function getView($nick)
    {
        return $this->getConfigValue($nick, self::KEY_VIEW);
    }

    public function getStatus($nick)
    {
        return $this->getConfigValue($nick, self::KEY_STATUS);
    }

    public function setStatus($nick, $status = self::STATUS_NOT_STARTED)
    {
        $this->setConfigValue($nick, self::KEY_STATUS, $status);
    }

    public function getStep($nick)
    {
        return $this->getConfigValue($nick, self::KEY_STEP);
    }

    public function setStep($nick, $step = NULL)
    {
        $this->setConfigValue($nick, self::KEY_STEP, $step);
    }

    public function getPriority($nick)
    {
        return $this->getConfigValue($nick, self::KEY_PRIORITY);
    }

    public function getType($nick)
    {
        return $this->getConfigValue($nick, self::KEY_TYPE);
    }

    // ########################################

    /**
     * @param string $view
     * @return null|Ess_M2ePro_Model_Wizard
     */
    public function getActiveWizard($view)
    {
        $wizards = $this->getAllWizards($view);

        /** @var $wizard Ess_M2ePro_Model_Wizard */
        foreach ($wizards as $wizard) {
            if ($this->isNotStarted($this->getNick($wizard)) || $this->isActive($this->getNick($wizard))) {
                return $wizard;
            }
        }

        return null;
    }

    // ------------------------------------------------------

    private function getAllWizards($view)
    {
        (is_null($this->cache) || Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) && $this->loadCache();

        $wizards = array();
        foreach ($this->cache as $nick => $wizard) {
            if ($wizard['view'] != '*' && $wizard['view'] != $view) {
                continue;
            }

            $wizards[] = $this->getWizard($nick);
        }

        return $wizards;
    }

    // ########################################

    /**
     * @param string $block
     * @param string $nick
     * @return Mage_Core_Block_Abstract
     * */

    public function createBlock($block,$nick = '')
    {
        return Mage::getSingleton('core/layout')->createBlock(
            'M2ePro/adminhtml_wizard_'.$nick.'_'.$block,
            null,
            array('nick' => $nick)
        );
    }

    // ########################################

    public function addWizardHandlerJs()
    {
        Mage::getSingleton('core/layout')->getBlock('head')->addJs(
            'M2ePro/WizardHandler.js'
        );
    }

    // ########################################

    public function getNick($wizard)
    {
        $parts = explode('_',get_class($wizard));
        $nick = array_pop($parts);
        $nick{0} = strtolower($nick{0});
        return $nick;
    }

    // ########################################

    private function loadCache()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = Mage::getSingleton('core/resource')->getTableName('m2epro_wizard');

        $this->cache = $connRead->fetchAll(
            $connRead->select()->from($tableName,'*')
        );

        $sortFunction = <<<FUNCTION
if (\$a['type'] != \$b['type']) {
    return \$a['type'] == Ess_M2ePro_Helper_Module_Wizard::TYPE_BLOCKER ? - 1 : 1;
}

if (\$a['priority'] == \$b['priority']) {
    return 0;
}

return \$a['priority'] > \$b['priority'] ? 1 : -1;
FUNCTION;

        $sortFunction = create_function('$a,$b',$sortFunction);

        usort($this->cache, $sortFunction);

        foreach ($this->cache as $id => $wizard) {
            $this->cache[$wizard['nick']] = $wizard;
            unset($this->cache[$id]);
        }

        Mage::helper('M2ePro/Data_Cache_Permanent')->setValue('wizard',
                                                    json_encode($this->cache),
                                                    array('wizard'),
                                                    60*60);
    }

    // ########################################

    public function getActiveBlockerWizard($view)
    {
        $wizards = $this->getAllWizards($view);

        /** @var $wizard Ess_M2ePro_Model_Wizard */
        foreach ($wizards as $wizard) {

            if ($this->getType($this->getNick($wizard)) != self::TYPE_BLOCKER) {
                continue;
            }

            if ($this->isNotStarted($this->getNick($wizard)) || $this->isActive($this->getNick($wizard))) {
                return $wizard;
            }
        }

        return null;
    }

    // ########################################
}