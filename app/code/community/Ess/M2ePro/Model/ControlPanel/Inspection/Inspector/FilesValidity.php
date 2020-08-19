<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_FilesValidity
    extends Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface
{
    //########################################

    public function getTitle()
    {
        return 'Files validity';
    }

    public function getGroup()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::GROUP_STRUCTURE;
    }

    public function getExecutionSpeed()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::EXECUTION_SPEED_FAST;
    }

    //########################################

    public function process()
    {
        $issues = array();

        try {
            $responseData = $this->getDiff();
        } catch (Exception $exception) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                $exception->getMessage()
            );

            return $issues;
        }

        if (empty($responseData['files_info'])) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createNotice(
                $this,
                'No info for this M2E Pro version.'
            );

            return $issues;
        }

        $problems = array();

        $baseDir = Mage::getBaseDir() . '/';
        foreach ($responseData['files_info'] as $info) {
            if (!is_file($baseDir . $info['path'])) {
                $problems[] = array(
                    'path' => $info['path'],
                    'reason' => 'File is missing'
                );
                continue;
            }

            $fileContent = trim(file_get_contents($baseDir . $info['path']));
            $fileContent = str_replace(array("\r\n", "\n\r", PHP_EOL), chr(10), $fileContent);

            if (Zend_Crypt::hash('md5', $fileContent) !== $info['hash']) {
                $problems[] = array(
                    'path' => $info['path'],
                    'reason' => 'Hash mismatch'
                );
                continue;
            }
        }

        if (!empty($problems)) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
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

            $html .= <<<HTML
<tr>
    <td>
        {$item['path']}
    </td>
    <td>
        {$item['reason']}
    </td>
    <td style="text-align: center;">
        <a href="{$url}" target="_blank">Diff</a>
    </td>
</tr>

HTML;
        }

        $html .= '</table>';
        return $html;
    }

    //########################################
}