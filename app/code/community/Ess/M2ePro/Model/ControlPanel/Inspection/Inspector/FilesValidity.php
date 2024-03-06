<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_FilesValidity
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface
{
    //########################################

    public function process()
    {
        $issues = array();

        try {
            $responseData = $this->getDiff();
        } catch (Exception $exception) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                $exception->getMessage()
            );

            return $issues;
        }

        if (empty($responseData['files_info'])) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'No info for this M2E Pro version.'
            );

            return $issues;
        }

        $problems = array();
        $serverFiles = array();

        foreach ($responseData['files_info'] as $info) {
            $serverFiles[$info['path']] = $info['hash'];
        }

        $clientFiles = $this->getClientFiles();

        foreach ($clientFiles as $path => $hash) {
            if (!isset($serverFiles[$path])) {
                $problems[] = array(
                    'path' => $path,
                    'reason' => 'New file detected',
                );
            }
        }

        foreach ($serverFiles as $path => $hash) {
            if (!isset($clientFiles[$path])) {
                $problems[] = array(
                    'path' => $path,
                    'reason' => 'File is missing',
                );
                continue;
            }

            if ($clientFiles[$path] != $hash) {
                $problems[] = array(
                    'path' => $path,
                    'reason' => 'Hash mismatch',
                );
            }
        }

        if (!empty($problems)) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Wrong files validity',
                $this->renderMetadata($problems)
            );
        }

        return $issues;
    }

    //########################################

    protected function getDiff()
    {
        $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('files', 'get', 'info');
        $dispatcherObject->process($connectorObj);

        return $connectorObj->getResponseData();
    }

    private function getClientFiles()
    {
        $includedFilesAndDirs = array(
            'app/code/community/Ess/M2ePro',
            'app/design/adminhtml/default/default/layout/M2ePro.xml',
            'app/design/adminhtml/default/default/template/M2ePro',
            'app/etc/modules/Ess_M2ePro.xml',
            'app/locale/de_AT/Ess_M2ePro.csv',
            'app/locale/de_CH/Ess_M2ePro.csv',
            'app/locale/de_DE/Ess_M2ePro.csv',
            'app/locale/es_ES/Ess_M2ePro.csv',
            'app/locale/fr_CA/Ess_M2ePro.csv',
            'app/locale/fr_FR/Ess_M2ePro.csv',
            'app/locale/it_CH/Ess_M2ePro.csv',
            'app/locale/it_IT/Ess_M2ePro.csv',
            'js/M2ePro',
            'skin/adminhtml/default/default/M2ePro',
            'skin/adminhtml/default/enterprise/M2ePro',
        );

        $excludedFilesAndDirs = array(
            'app/code/community/Ess/M2ePro/sql/Update/Config.php',
            'app/code/community/Ess/M2ePro/sql/Update/dev'
        );

        $clientFiles = array();

        foreach ($includedFilesAndDirs as $dir) {
            if (!in_array($dir, $excludedFilesAndDirs) && is_dir($dir)) {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
                foreach ($iterator as $file) {
                    $filePath = $file->getPathname();
                    if (
                        $file->isFile()
                        && !in_array($filePath, $excludedFilesAndDirs)
                        && !$this->isExcludedDirectory($filePath, $excludedFilesAndDirs)
                    ) {
                        $fileContent = trim(file_get_contents($filePath));
                        $fileContent = str_replace(array("\r\n", "\n\r", PHP_EOL), chr(10), $fileContent);
                        $clientFiles[$filePath] = Zend_Crypt::hash('md5', $fileContent);
                    }
                }
            } elseif (!in_array($dir, $excludedFilesAndDirs) && is_file($dir)) {
                $fileContent = trim(file_get_contents($dir));
                $fileContent = str_replace(array("\r\n", "\n\r", PHP_EOL), chr(10), $fileContent);
                $clientFiles[$dir] = Zend_Crypt::hash('md5', $fileContent);
            }
        }

        return $clientFiles;
    }

    private function isExcludedDirectory($filePath, $excludedFilesAndDirs)
    {
        foreach ($excludedFilesAndDirs as $excludedItem) {
            if (strpos($filePath, $excludedItem) !== false) {
                return true;
            }
        }
        return false;
    }

    //########################################

    protected function renderMetadata($data)
    {
        $html = <<<HTML
<table>
    <tr>
        <th style="width: 600px">Path</th>
        <th>Reason</th>
        <th>Action</th>
    </tr>
HTML;
        foreach ($data as $item) {
            $url = Mage::helper('adminhtml')->getUrl(
                '*/adminhtml_ControlPanel_tools_m2ePro_install/filesDiff',
                array('filePath' => base64_encode($item['path']))
            );

            $link = ($item['reason'] === 'New file detected') ? '' : "<a href='$url' target='_blank'>Diff</a>";

            $html .= <<<HTML
<tr>
    <td>
        {$item['path']}
    </td>
    <td>
        {$item['reason']}
    </td>
    <td style="text-align: center;">
        {$link}
    </td>
</tr>

HTML;
        }

        $html .= '</table>';
        return $html;
    }

    //########################################
}