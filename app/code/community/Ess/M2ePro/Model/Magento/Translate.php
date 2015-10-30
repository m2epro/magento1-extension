<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Translate extends Mage_Core_Model_Translate
{
    const TRANSLATE_MODE_NORMAL = 0;
    const TRANSLATE_MODE_CUSTOM = 1;

    const TRANSLATE_AREA = 'adminhtml';

    const MODULE_NAME = 'Ess_M2ePro';

    protected $mode;

    //########################################

    /**
     * @param mixed $mode
     */
    protected function setMode($mode)
    {
        $this->mode = $mode;
    }
    /**
     * @return mixed
     */
    protected function getMode()
    {
        return $this->mode;
    }

    public function __construct($mode = self::TRANSLATE_MODE_CUSTOM)
    {
        $this->setMode($mode);
    }

    public function init($area = self::TRANSLATE_AREA, $forceReload = false)
    {
        // regular object returned
        if ($this->getMode() == self::TRANSLATE_MODE_NORMAL) {
            return Mage::app()->getTranslator()->init(self::TRANSLATE_AREA, $forceReload);
        }

        $this->setConfig(array(
            self::CONFIG_KEY_AREA => self::TRANSLATE_AREA
        ));

        $this->_translateInline = Mage::getSingleton('core/translate_inline')
            ->isAllowed($area=='adminhtml' ? 'admin' : null);

        if (!$forceReload) {
            if ($this->_canUseCache()) {
                $this->_data = $this->_loadCache();
                if ($this->_data !== false) {
                    return $this;
                }
            }
            Mage::app()->removeCache($this->getCacheId());
        }

        $this->_data = array();

        $modulesConfig = $this->getModulesConfig();

        if (isset($modulesConfig->{self::MODULE_NAME})) {
            $info = $modulesConfig->{self::MODULE_NAME}->asArray();
            $this->_loadModuleTranslation(self::MODULE_NAME, $info['files'], $forceReload);
        }

        if (!$forceReload && $this->_canUseCache()) {
            $this->_saveCache();
        }

        return $this;
    }

    public function getCacheId()
    {
        $this->_cacheId = self::MODULE_NAME.'_'.parent::getCacheId();
        return $this->_cacheId;
    }

    public function __()
    {
        $args = func_get_args();
        return parent::translate($args);
    }

    //########################################
}