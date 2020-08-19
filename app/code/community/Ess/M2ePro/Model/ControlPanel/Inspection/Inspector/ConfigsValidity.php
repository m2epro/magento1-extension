<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_ConfigsValidity
    extends Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface
{
    //########################################

    public function getTitle()
    {
        return 'Configs validity';
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

        if (!isset($responseData['configs_info'])) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createNotice(
                $this,
                'No info for this M2E Pro version.'
            );

            return $issues;
        }

        $difference = $this->getSnapshot($responseData['configs_info']);

        if (!empty($difference)) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                'Missing configs',
                $this->renderMetadata($difference)
            );
        }

        return $issues;
    }

    //########################################

    protected function getDiff()
    {
        $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('configs', 'get', 'info');
        $dispatcherObject->process($connectorObj);
        return $connectorObj->getResponseData();
    }

    protected function getSnapshot($data)
    {
        $currentData = array();

        foreach ($data as $tableName => $configInfo) {
            $currentData[$tableName] = Mage::helper('M2ePro/Module_Database_Structure')
                ->getConfigSnapshot($tableName);
        }

        $differences = array();

        foreach ($data as $tableName => $configInfo) {
            foreach ($configInfo as $codeHash => $item) {
                if (array_key_exists($codeHash, $currentData[$tableName])) {
                    continue;
                }

                $differences[] = array('table' => $tableName,
                    'item' => $item,
                    'solution' => 'insert');
            }
        }

        return $differences;
    }

    //########################################

    protected function renderMetadata($data)
    {
        $html = <<<HTML
<table style="width: 100%;">
    <tr>
        <th style="width: 200px">Group</th>
        <th style="width: 200px">Key</th>
        <th style="width: 150px">Value</th>
        <th style="width: 50px">Action</th>
    </tr>
HTML;

        foreach ($data as $index => $row) {
            $url = Mage::helper('adminhtml')->getUrl(
                '*/adminhtml_controlPanel_database/addTableRow', array(
                    'table' => $row['table'],
                    'model' => Mage::helper('M2ePro/Module_Database_Structure')->getTableModel($row['table']),
                )
            );

            $actionWord = 'Insert';
            $styles = '';
            $onclickAction = <<<JS
var elem     = $(this.id),
    formData = Form.serialize(elem.up('tr').down('form'));

elem.up('tr').remove();

new Ajax.Request( '{$url}' , {
    method: 'get',
    asynchronous : true,
    parameters : formData
});
JS;
            $group = $row['item']['group'] === null ? 'null' : $row['item']['group'];
            $key = $row['item']['key'] === null ? 'null' : $row['item']['key'];
            $value = $row['item']['value'] === null ? 'null' : $row['item']['value'];

            $html .= <<<HTML
<tr>
    <td>{$row['item']['group']}</td>
    <td>{$row['item']['key']}</td>
    <td>
        <form style="margin-bottom: 0; display: block; height: 20px">
            <input type="text"   name="value_value" value="{$value}">
            <input type="checkbox" name="cells[]" value="group" style="display: none;" checked="checked">
            <input type="checkbox" name="cells[]" value="key" style="display: none;" checked="checked">
            <input type="checkbox" name="cells[]" value="value" style="display: none;" checked="checked">
            <input type="hidden" name="value_group" value="{$group}">
            <input type="hidden" name="value_key" value="{$key}">
        </form>
    </td>
    <td>
        <a id="insert_id_{$index}" style= "{$styles}"
           onclick="{$onclickAction}" href="javascript:void(0);">{$actionWord}</a>
    </td>
</tr>
HTML;
        }

        $html .='</table>';
        return $html;
    }

    //########################################
}