<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_LocalPoolOverwrites
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface
{
    /**@var array */
    protected $_overwrittenFiles;

    /**@var bool */
    protected $_extensionFilesOverwritten = false;

    //########################################

    public function process()
    {
        $issues = array();
        $this->checkLocalPool();

        if ($this->_extensionFilesOverwritten) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Local Pool overwrites extension files',
                $this->renderMetadata($this->_overwrittenFiles)
            );
        } elseif (!empty($this->_overwrittenFiles)) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Local Pool overwrites',
                $this->renderMetadata($this->_overwrittenFiles)
            );
        }

        return $issues;
    }

    protected function checkLocalPool()
    {
        $paths = array(
            Mage::getBaseDir() . '/app/code/local/Mage',
            Mage::getBaseDir() . '/app/code/local/Zend',
            Mage::getBaseDir() . '/app/code/local/Ess',
            Mage::getBaseDir() . '/app/code/local/Varien',
        );

        $overwrites = array();
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $directoryIterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

            /** @var SplFileInfo $splFileObj */
            foreach ($iterator as $splFileObj) {
                $splFileObj->isFile() && $overwrites[] = $splFileObj->getRealPath();
            }
        }

        foreach ($overwrites as $item) {
            if ($this->isOriginalFileExists($item)) {
                $relativePath = str_replace(Mage::getBaseDir() . DS, '', $item);
                if (strpos($relativePath, 'app/code/local/Ess') !== false) {
                    $this->_extensionFilesOverwritten = true;
                }

                $this->_overwrittenFiles[] = $relativePath;
            }
        }
    }

    protected function isOriginalFileExists($overwritedFilename)
    {
        $unixFormattedPath = str_replace('\\', '/', $overwritedFilename);

        $isOriginalCoreFileExist = is_file(str_replace('/local/', '/core/', $unixFormattedPath));
        $isOriginalCommunityFileExist = is_file(str_replace('/local/', '/community/', $unixFormattedPath));
        $isOriginalLibFileExist = is_file(str_replace('app/code/local/', 'lib/', $unixFormattedPath));

        return $isOriginalCoreFileExist || $isOriginalCommunityFileExist || $isOriginalLibFileExist;
    }

    protected function renderMetadata($data)
    {
        $html = <<<HTML
<table>
    <tr>
        <th style="width:900px">Path</th>
        <th style="width: 50px"></th>
    </tr>
HTML;
        foreach ($data as $item) {
            $diffHtml = '';
            $color = '#333';
            if (strpos(strtolower($item), 'm2epro') !== false) {
                $originalPath = str_replace('local', 'community', $item);
                $url = Mage::helper('adminhtml')->getUrl(
                    '*/adminhtml_ControlPanel_tools_m2ePro_install/filesDiff',
                    array('filePath' => base64_encode($item), 'originalPath' => base64_encode($originalPath))
                );
                $diffHtml = '<a href="' . $url . '" target="_blank">Diff</a>';
                $color = '#FF0000';
            }

            $html .= <<<HTML
<tr>
    <td style="color: {$color}">{$item}</td>
    <td>{$diffHtml}</td>
</tr>
HTML;
        }

        $html .= '</table>';
        return $html;
    }

    //########################################
}