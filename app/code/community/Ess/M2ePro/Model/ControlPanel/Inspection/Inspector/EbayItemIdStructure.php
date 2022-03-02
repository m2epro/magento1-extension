<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_EbayItemIdStructure
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface,
    Ess_M2ePro_Model_ControlPanel_Inspection_FixerInterface
{
    //########################################

    public function process()
    {
        $issues = array();
        $brokenItems = array();

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->getSelect()->joinLeft(
            array('ei' => Mage::getResourceModel('M2ePro/Ebay_Item')->getMainTable()),
            '`second_table`.`ebay_item_id` = `ei`.`id`',
            array('item_id' => 'item_id')
        );
        $collection->addFieldToFilter(
            'status',
            array('nin' => array(Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
                Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN))
        );

        $collection->addFieldToFilter('item_id', array('null' => true));

        if ($total = $collection->getSize()) {
            $brokenItems = array(
                'total' => $total,
                'ids' => $collection->getAllIds()
            );
        }

        if (!empty($brokenItems)) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Ebay item id N\A',
                $this->renderMetadata($brokenItems)
            );
        }

        return $issues;
    }

    protected function renderMetadata($data)
    {
        $formKey = Mage::getSingleton('core/session')->getFormKey();
        $currentUrl = Mage::helper('adminhtml')
            ->getUrl('*/adminhtml_controlPanel_tools_m2ePro_general/repairEbayItemIdStructure');

        $html = <<<HTML
    <form method="POST" action="{$currentUrl}">
    <input type="hidden" name="form_key" value="{$formKey}">
<table>
    <tr>
        <th style="width: 200px"></th>
        <th style="width: 300px"></th>
    </tr>
HTML;

        $repairInfo = Mage::helper('M2ePro')->jsonEncode($data['ids']);
        $input = "<input type='checkbox' style='display: none;' checked='checked'
        name='repair_info' value='" . $repairInfo . "'>";
        $html .= <<<HTML
<tr>
    <td> Total Ebay items with id N\A: {$data['total']}</td>
    <td>{$input}</td>
</tr>
HTML;
        $html .= '</table>
<button type="button" onclick="ControlPanelInspectionObj.removeRow(this)">Repair</button>
</form>';

        return $html;
    }

    public function fix($ids)
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $ids));

        /** @var $item Ess_M2ePro_Model_Order_Item */
        while ($item = $collection->fetchItem()) {
            $item->setData('status', Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)->save();
        }
    }

    //########################################
}