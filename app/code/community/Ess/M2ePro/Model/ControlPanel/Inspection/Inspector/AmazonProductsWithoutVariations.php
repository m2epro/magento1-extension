<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_AmazonProductsWithoutVariations
    extends Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface,
    Ess_M2ePro_Model_ControlPanel_Inspection_FixerInterface
{
    //########################################

    public function getTitle()
    {
        return 'Amazon products without variations';
    }

    public function getGroup()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::GROUP_PRODUCTS;
    }

    public function getExecutionSpeed()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::EXECUTION_SPEED_FAST;
    }

    //########################################

    public function process()
    {
        $issues = array();
        $brokenItems = array();

        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->joinLeft(
            array('mlpv' => Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable()),
            '`second_table`.`listing_product_id` = `mlpv`.`listing_product_id`',
            array()
        );
        $collection->addFieldToFilter('is_variation_product', 1);
        $collection->addFieldToFilter('is_variation_product_matched', 1);
        $collection->addFieldToFilter('mlpv.id', array('null' => true));

        if ($total = $collection->getSize()) {
            $brokenItems = array(
                'total' => $total,
                'ids' => $collection->getAllIds()
            );
        }

        if (!empty($brokenItems)) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                'Has products without variation',
                $this->renderMetadata($brokenItems)
            );
        }

        return $issues;
    }

    protected function renderMetadata($data)
    {
        $formKey = Mage::getSingleton('core/session')->getFormKey();
        $currentUrl = Mage::helper('adminhtml')
            ->getUrl('*/adminhtml_controlPanel_tools_m2ePro_general/repairAmazonProductWithoutVariations');

        $html = <<<HTML
    <form method="POST" action="{$currentUrl}">
    <input type="hidden" name="form_key" value="{$formKey}">
<table>
    <tr>
        <th style="width: 300px"></th>
        <th style="width: 300px"></th>
    </tr>
HTML;

        $repairInfo = Mage::helper('M2ePro')->jsonEncode($data['ids']);
        $input = "<input type='checkbox' style='display: none;' checked='checked' 
        name='repair_info' value='" . $repairInfo . "'>";
        $html .= <<<HTML
<tr>
    <td> Total amazon product without variation: {$data['total']}</td>
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
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $ids));

        while ($item = $collection->fetchItem()) {
            $item->getChildObject()->setData('is_variation_product_matched', 0)->save();
        }
    }

    //########################################
}