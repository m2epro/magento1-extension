<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Requirements_Reader
{
    protected $_cachedData;

    //########################################

    public function __construct()
    {
        $requirementsFile = Mage::getConfig()->getModuleDir(NULL, Ess_M2ePro_Helper_Module::IDENTIFIER) .DS.
                            'requirements.json';

        $requirements = Mage::helper('M2ePro/Data')->jsonDecode(file_get_contents($requirementsFile));

        $composerFile = Mage::getConfig()->getModuleDir(NULL, Ess_M2ePro_Helper_Module::IDENTIFIER) .DS.
                        'composer.json';

        $composerData = Mage::helper('M2ePro/Data')->jsonDecode(file_get_contents($composerFile));
        $requirements['composer'] = $composerData['require'];

        $this->_cachedData = $requirements;
    }

    //########################################

    public function getMemoryLimitData($dataPart = NULL)
    {
        $path = array_filter(array('memory_limit', $dataPart));
        return $this->getPath($path);
    }

    public function getExecutionTimeData($dataPart = NULL)
    {
        $path = array_filter(array('execution_time', $dataPart));
        return $this->getPath($path);
    }

    public function getMagentoVersionData($dataPart = NULL)
    {
        $path = array_filter(array('magento_version', $dataPart));
        return $this->getPath($path);
    }

    public function gePhpVersionData()
    {
        return $this->getPath(array('composer', 'php'));
    }

    // ---------------------------------------

    protected function getPath(array $path, $data = null)
    {
        $data === null && $data = $this->_cachedData;
        $pathPart = array_shift($path);

        if (isset($data[$pathPart])) {
            return !empty($path) ? $this->getPath($path, $data[$pathPart]) : $data[$pathPart];
        }

        return NULL;
    }

    //########################################
}
