<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View_Item_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    private $listingProductId;
    private $listingProduct;

    private $motorsType;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayMotorViewItemGrid');

        // Set default values
        //------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        $this->setPagerVisibility(false);
        $this->setDefaultLimit(false);
        //------------------------------
    }

    // ########################################

    protected function getExistingItems(array $ids)
    {
        $typeIdentifier = $this->getMotorsHelper()->getIdentifierKey($this->getMotorsType());

        $select = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from(
                $this->getMotorsHelper()->getDictionaryTable($this->getMotorsType()),
                array($typeIdentifier)
            )
            ->where('`'.$typeIdentifier.'` IN (?)', $ids);

        return $select->query()->fetchAll(PDO::FETCH_COLUMN);
    }

    protected function getCollectionItems()
    {
        $attributeValue = $this->getListingProduct()->getMagentoProduct()->getAttributeValue(
            $this->getMotorsHelper()->getAttribute($this->getMotorsType())
        );

        $parsedValue = $this->getMotorsHelper()->parseAttributeValue($attributeValue);
        if (empty($parsedValue)) {
            return array();
        }

        $existingItems = $this->getExistingItems(array_keys($parsedValue['items']));

        $items = array();
        foreach ($parsedValue['items'] as $id => $item) {

            if (!in_array($id, $existingItems)) {
                continue;
            }

            $itemData = array(
                'id'       => $id,
                'note'     => $item['note']
            );

            $items[$id] = new Varien_Object($itemData);
        }

        return $items;
    }

    protected function _prepareCollection()
    {
        $collection = new Varien_Data_Collection();
        foreach ($this->getCollectionItems() as $item) {
            $collection->addItem($item);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('item', array(
            'header' => Mage::helper('M2ePro')->__($this->getItemsColumnTitle()),
            'align'  => 'left',
            'type'   => 'text',
            'index'  => 'id',
            'width'  => '50px',
            'frame_callback' => array($this, 'callbackColumnIdentifier'),
            'filter_condition_callback' => array($this, 'customColumnFilter')
        ));

        $this->addColumn('note', array(
            'header'       => Mage::helper('M2ePro')->__('Note'),
            'align'        => 'left',
            'type'         => 'text',
            'index'        => 'note',
            'width'        => '350px',
            'filter_index' => 'note',
            'filter_condition_callback' => array($this, 'customColumnFilter')
        ));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setUseSelectAll(false);
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // Set mass-action
        //--------------------------------
        $this->getMassactionBlock()->addItem('removeItem', array(
            'label'   => Mage::helper('M2ePro')->__('Remove'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    protected function getNoFilterMassactionColumn()
    {
        return true;
    }

    public function getMassactionBlockName()
    {
        // this is required for correct work of massaction js
        return 'M2ePro/adminhtml_grid_massaction';
    }

    //########################################

    public function callbackColumnIdentifier($value, $row, $column, $isExport)
    {
        return $value;
    }

    // ####################################

    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection() && $column->getFilterConditionCallback()) {
            call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
        }
        return $this;
    }

    //########################################

    protected function customColumnFilter($collection, $column)
    {
        $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
        $condition = $column->getFilter()->getCondition();
        $value = array_pop($condition);

        if ($field && isset($condition)) {
            $this->filterByField($field, $value);
        }

        return $this;
    }

    //--------------------------------

    protected function filterByField($field, $value)
    {
        $filteredCollection = new Varien_Data_Collection();
        $value = str_replace(array(' ','%','\\','\''),'',$value);

        foreach ($this->getCollection()->getItems() as $item) {
            if (strpos($item->getData($field),$value) !== false) {
                $filteredCollection->addItem($item);
            }
        }
        $this->setCollection($filteredCollection);
    }

    // ####################################

    protected function _setCollectionOrder($column)
    {
        $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();
        $direction = $column->getDir();

        if ($field && isset($direction)) {
            $this->_orderByColumn($field, $direction);
        }

        return $this;
    }

    //--------------------------------

    protected function _orderByColumn($column, $direction)
    {
        $sortedCollection = new Varien_Data_Collection();

        $collection = $this->getCollection()->toArray();
        $collection = $collection['items'];

        $sortByColumn = array();
        foreach ($collection as $item) {
            $sortByColumn[] = $item[$column];
        }

        strtolower($direction) == 'asc' && array_multisort($sortByColumn, SORT_ASC, $collection);
        strtolower($direction) == 'desc' && array_multisort($sortByColumn, SORT_DESC, $collection);

        foreach ($collection as $item) {
            $sortedCollection->addItem(new Varien_Object($item));
        }
        $this->setCollection($sortedCollection);
    }

    // ####################################

    protected function _toHtml()
    {
        $additionalHtml = <<<HTML
<style type="text/css">
    #{$this->getId()} table td, #{$this->getId()} table th {
        padding: 5px;
    }
</style>
HTML;

        $additionalHtml .= '<script type="text/javascript">';

        if ($this->canDisplayContainer()) {
            $additionalHtml .= <<<JS
EbayMotorViewItemGridHandlerObj = new EbayMotorViewItemGridHandler(
    '{$this->getId()}',
    '{$this->getListingProductId()}'
);
JS;
        }

        $additionalHtml .= <<<JS
EbayMotorViewItemGridHandlerObj.afterInitPage();
JS;

        $additionalHtml .= '</script>';

        return '<div style="height: 350px; overflow: auto;">' .
            parent::_toHtml()
            . '</div>' .
            $additionalHtml;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_motor/viewItemGrid', array(
            '_current' => true
        ));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    public function setMotorsType($motorsType)
    {
        $this->motorsType = $motorsType;
    }

    public function getMotorsType()
    {
        if (is_null($this->motorsType)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Motors type not set.');
        }

        return $this->motorsType;
    }

    //########################################

    public function getItemsColumnTitle()
    {
        if ($this->getMotorsType() == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID) {
            return Mage::helper('M2ePro')->__('ePID');
        }

        return Mage::helper('M2ePro')->__('kType');
    }

    //########################################

    public function getListingProduct()
    {
        if (is_null($this->listingProduct)) {
            $this->listingProduct = Mage::helper('M2ePro/Component_Ebay')
                ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->listingProduct;
    }

    //########################################

    /**
     * @return null
     */
    public function getListingProductId()
    {
        return $this->listingProductId;
    }

    /**
     * @param null $listingProductId
     */
    public function setListingProductId($listingProductId)
    {
        $this->listingProductId = $listingProductId;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Helper_Component_Ebay_Motors
     */
    private function getMotorsHelper()
    {
        return Mage::helper('M2ePro/Component_Ebay_Motors');
    }

    //########################################
}