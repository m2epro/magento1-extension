<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_FilesPermissions
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface
{
    /** @var array */
    protected $_unWritable = array();

    /** @var array */
    protected $_notExisting = array();

    /** @var array */
    protected $_checked = array();

    //########################################

    public function process()
    {
        $this->processRootDirs();
        $this->processModuleFiles();

        $issues = array();

        if (!empty($this->_unWritable)) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Has unwriteable files \ directories',
                array_keys($this->_unWritable)
            );
        }

        if (!empty($this->_notExisting)) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Following directories are missing',
                $this->_notExisting
            );
        }

        return $issues;
    }

    protected function processRootDirs()
    {
        $rootDirs = array(
            'app/code/community/Ess/M2ePro',
            'js/M2ePro',
            'skin/adminhtml/default/default/M2ePro',
            'skin/adminhtml/default/enterprise/M2ePro',
            'app/design/adminhtml/default/default/template/M2ePro',
            'app/design/adminhtml/default/default/layout',
            'app/etc/modules',
            'app/locale/'
        );

        foreach ($rootDirs as $path) {
            $currentPath = null;
            foreach (explode('/', $path) as $pathPart) {
                $currentPath .= DS.$pathPart;
                $fullPath = Mage::getBaseDir().$currentPath;

                if (file_exists($fullPath) && !is_dir_writeable($fullPath)) {
                    $this->_unWritable[$fullPath] = true;
                }

                $this->_checked[$fullPath] = true;
            }
        }
    }

    protected function processModuleFiles()
    {
        $modulePaths = array(
            'app/code/community/Ess/M2ePro',
            'js/M2ePro',
            'skin/adminhtml/default/default/M2ePro',
            'skin/adminhtml/default/enterprise/M2ePro',
            'app/design/adminhtml/default/default/template/M2ePro',
        );

        foreach ($modulePaths as $path) {
            $fullPath = Mage::getBaseDir().DS.$path;

            try {
                $directoryIterator = new RecursiveDirectoryIterator($fullPath, FilesystemIterator::SKIP_DOTS);
            } catch (\UnexpectedValueException $e) {
                $this->_notExisting[] = $fullPath;
                continue;
            }

            $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

            foreach ($iterator as $fileObj) {
                /**@var \SplFileObject $fileObj */
                $this->check($fileObj);
            }
        }

        $moduleFiles = array(
            'app/etc/modules/Ess_M2ePro.xml',
            'app/design/adminhtml/default/default/layout/M2ePro.xml'
        );

        foreach ($moduleFiles as $path) {
            $fullPath = Mage::getBaseDir().DS.$path;

            if (file_exists($fullPath) && !is_writable($fullPath)) {
                $this->_unWritable[$fullPath] = true;
            }

            $this->_checked[$fullPath] = true;
        }
    }

    protected function check(\SplFileInfo $object)
    {
        if (isset($this->_unWritable[$object->getRealPath()])) {
            return;
        }

        if ($object->isDir() && file_exists($object->getRealPath()) && !is_dir_writeable($object->getRealPath())) {
            $this->_unWritable[$object->getRealPath()] = true;
        }

        if ($object->isFile() && file_exists($object->getRealPath()) && !is_writable($object->getRealPath())) {
            $this->_unWritable[$object->getRealPath()] = true;
        }

        $this->_checked[$object->getRealPath()] = true;
    }

    //########################################
}