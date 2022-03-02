<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_ShowM2eProLoggers
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface
{
    /** @var array */
    protected $_loggers = array();

   //########################################

    public function process()
    {
        $issues = array();
        $this->searchLoggers();

        if (!empty($this->_loggers)) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'M2ePro loggers were found in magento files',
                $this->_loggers
            );
        }

        return $issues;
    }

    protected function searchLoggers()
    {
        $recursiveIteratorIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(Mage::getBaseDir(), FilesystemIterator::FOLLOW_SYMLINKS)
        );

        foreach ($recursiveIteratorIterator as $splFileInfo) {
            /**@var \SplFileInfo $splFileInfo */

            if (!$splFileInfo->isFile() ||
                !in_array($splFileInfo->getExtension(), array('php', 'phtml'))) {
                continue;
            }

            if (strpos($splFileInfo->getRealPath(), 'Ess' . DS . 'M2ePro') !== false ||
                strpos($splFileInfo->getRealPath(), 'Ess_M2ePro') !== false) {
                continue;
            }

            $splFileObject = $splFileInfo->openFile();
            if (!$splFileObject->getSize()) {
                continue;
            }

            foreach ($splFileObject as $line => $contentRow) {
                if (strpos($contentRow, 'M2ePro/Module_Logger') === false) {
                    continue;
                }

                $this->_loggers[] = $splFileObject->getRealPath() . ' in line ' . $line;
            }
        }
    }

    //########################################
}