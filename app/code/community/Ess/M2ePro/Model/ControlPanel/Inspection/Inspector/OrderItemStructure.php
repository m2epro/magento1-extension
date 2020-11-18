<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_OrderItemStructure
    extends Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface,
    Ess_M2ePro_Model_ControlPanel_Inspection_FixerInterface
{
    //########################################

    public function getTitle()
    {
        return 'Order item structure';
    }

    public function getGroup()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::GROUP_ORDERS;
    }

    public function getExecutionSpeed()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::EXECUTION_SPEED_FAST;
    }

    //########################################

    public function process()
    {
        $issues = array();

        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Order_Item')->getCollection();
        $collection->getSelect()->joinLeft(
            array('mo' => $collection->getResource()->getTable('M2ePro/Order')),
            'main_table.order_id=mo.id',
            array()
        );
        $collection->addFieldToFilter('mo.id', array('null' => true));

        if ($total = $collection->getSize()) {
            $brokenOrdersItem = array(
                'total' => $total,
                'ids' => $collection->getAllIds()
            );
        }

        if (!empty($brokenOrdersItem)) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                'Has broken order item',
                $this->renderMetadata($brokenOrdersItem)
            );
        }

        return $issues;
    }

    protected function renderMetadata($data)
    {
        $formKey = Mage::getSingleton('core/session')->getFormKey();
        $currentUrl = Mage::helper('adminhtml')
            ->getUrl('*/adminhtml_controlPanel_tools_m2ePro_general/repairOrderItemStructure');

        $html = <<<HTML
    <form method="POST" action="{$currentUrl}">
    <input type="hidden" name="form_key" value="{$formKey}">
<table>
    <tr>
        <th style="width: 150px"></th>
        <th style="width: 300px"></th>
    </tr>
HTML;
            $repairInfo = Mage::helper('M2ePro')->jsonEncode($data['ids']);
            $input = "<input type='checkbox' style='display: none;' checked='checked'
            name='repair_info' value='" . $repairInfo . "'>";
            $html .= <<<HTML
<tr>
    <td>Total broken items ({$data['total']})</td>
    <td>{$input}</td>
</tr>
HTML;
        $html .= '</table>
<button type="button" onclick="ControlPanelInspectionObj.removeRow(this)">Delete broken items</button>
</form>';

        return $html;
    }

    public function fix($ids)
    {
        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Order_Item')->getCollection();
        $collection->addFieldToFilter('id', array('in' => $ids));

        while ($item = $collection->fetchItem()) {
            $item->delete();
        }
    }

    //########################################
}