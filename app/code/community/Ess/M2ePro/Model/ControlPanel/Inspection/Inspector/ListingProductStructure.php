<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_ListingProductStructure
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface,
    Ess_M2ePro_Model_ControlPanel_Inspection_FixerInterface
{
    /**@var array */
    protected $_brokenData    = array();

    //########################################

    public function process()
    {
        $issues = array();

        $this->getBrokenOption();
        $this->getBrokenListing();
        $this->getBrokenVariation();
        $this->getBrokenListingProduct();

        if (!empty($this->_brokenData)) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Issue_Factory')->createIssue(
                'Has broken listing or listing product',
                $this->renderMetadata($this->_brokenData)
            );
        }

        return $issues;
    }

    //########################################

    protected function getBrokenOption()
    {
        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Listing_Product_Variation_Option')->getCollection();
        $collection->getSelect()->joinLeft(
            array('mlpv' => $collection->getResource()->getTable('M2ePro/Listing_Product_Variation')),
            'main_table.listing_product_variation_id=mlpv.id',
            array()
        );
        $collection->addFieldToFilter('mlpv.id', array('null' => true));
        $collection->getSelect()->group('main_table.id');

        if ($total = $collection->getSize()) {
            $this->_brokenData['broken_option'] = array(
                'table' => $collection->getMainTable(),
                'total' => $total,
                'ids'   => $collection->getAllIds()
            );
        }
    }

    protected function getBrokenVariation()
    {
        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Listing_Product_Variation')->getCollection();
        $collection->getSelect()->joinLeft(
            array('mlp' => $collection->getResource()->getTable('M2ePro/Listing_Product')),
            'main_table.listing_product_id=mlp.id',
            array()
        );
        $collection->getSelect()->joinLeft(
            array('mlpvo' => $collection->getResource()->getTable('M2ePro/Listing_Product_Variation_Option')),
            'main_table.id=mlpvo.listing_product_variation_id',
            array()
        );

        $collection->getSelect()->where('mlp.id IS NULL OR mlpvo.id IS NULL');
        $collection->getSelect()->group('main_table.id');

        if ($total = $collection->getSize()) {
            $this->_brokenData['broken_variation'] = array(
                'table' => $collection->getMainTable(),
                'total' => $total,
                'ids'   => $collection->getAllIds()
            );
        }
    }

    protected function getBrokenListingProduct()
    {
        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
        $collection->getSelect()->joinLeft(
            array('ml' => $collection->getResource()->getTable('M2ePro/Listing')),
            'main_table.listing_id=ml.id',
            array()
        );
        $collection->addFieldToFilter('ml.id', array('null' => true));
        $collection->getSelect()->group('main_table.id');

        if ($total = $collection->getSize()) {
            $this->_brokenData['broken_product'] = array(
                'table' => $collection->getMainTable(),
                'total' => $total,
                'ids'   => $collection->getAllIds()
            );
        }
    }

    protected function getBrokenListing()
    {
        /** @var $collection Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = Mage::getModel('M2ePro/Listing')->getCollection();
        $collection->getSelect()->joinLeft(
            array('ma' => $collection->getResource()->getTable('M2ePro/Account')),
            'main_table.account_id=ma.id',
            array()
        );
        $collection->addFieldToFilter('ma.id', array('null' => true));
        $collection->getSelect()->group('main_table.id');

        if ($total = $collection->getSize()) {
            $this->_brokenData['broken_listing'] = array(
                'table' => $collection->getMainTable(),
                'total' => $total,
                'ids'   => $collection->getAllIds()
            );
        }
    }

    //########################################

    protected function renderMetadata($data)
    {
        $formKey = Mage::getSingleton('core/session')->getFormKey();
        $currentUrl = Mage::helper('adminhtml')
            ->getUrl('*/adminhtml_controlPanel_tools_m2ePro_general/repairListingProductStructure');

        $html = <<<HTML
    <form method="POST" action="{$currentUrl}">
    <input type="hidden" name="form_key" value="{$formKey}">
<table>
    <tr>
        <th style="width: 150px"></th>
        <th style="width: 300px"></th>
    </tr>
HTML;

        foreach ($data as $key => $item) {
            $repairInfo =  Mage::helper('M2ePro')->jsonEncode($item);
            $description = str_replace('_', ' ', $key);
            $input = "<input type='checkbox' name='repair_info[]' value='" . $repairInfo . "'>";
            $html .= <<<HTML
<tr>
    <td>{$description} ({$item['total']})</td>
    <td>{$input}</td>
</tr>
HTML;
        }

        $html .= '</table>
<button type="button" onclick="ControlPanelInspectionObj.removeRow(this)">Delete checked</button>
</form>';

        return $html;
    }

    public function fix($data)
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        foreach ($data as $table => $ids) {
            $connWrite->delete(
                $table,
                '`id` IN (' . implode(',', $ids) . ')'
            );
        }
    }

    //########################################
}