<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Template_SellingFormat_TaxCodes_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $marketplaceId;
    private $noSelection;

    //########################################

    public function __construct($params)
    {
        parent::__construct();

        $this->setId('taxCodesGrid');
        $this->marketplaceId = (int)$params['marketplaceId'];
        $this->noSelection   = (bool)$params['noSelection'];

        // Set default values
        //------------------------------
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        $this->setPagerVisibility(true);
        $this->setDefaultLimit(30);
        //------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $connRead->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')
                    ->getTableNameWithPrefix('m2epro_walmart_dictionary_marketplace'),
                array('tax_codes')
            )
            ->where('marketplace_id = ?', $this->marketplaceId);

        $row = $select->query()->fetchColumn();

        $collection = new Ess_M2ePro_Model_Collection_Custom();
        foreach (Mage::helper('M2ePro')->jsonDecode($row) as $item) {
            $collection->addItem(new Varien_Object($item));
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn('tax_code', array(
            'header'         => Mage::helper('M2ePro')->__('Tax Code'),
            'align'          => 'left',
            'type'           => 'text',
            'index'          => 'tax_code',
            'width'          => '10px',
            'filter_condition_callback' => array($this, 'callbackFilterTaxCodes'),
            'sortable'       => false
        ));

        $this->addColumn('description', array(
            'header'         => Mage::helper('M2ePro')->__('Description'),
            'align'          => 'left',
            'type'           => 'text',
            'index'          => 'description',
            'width'          => '645px',
            'filter_condition_callback'         => array($this, 'callbackFilterDescription'),
            'sortable'       => false
        ));

        if (!$this->noSelection) {
            $this->addColumn('action', array(
                'header'         => Mage::helper('M2ePro')->__('Action'),
                'align'          => 'left',
                'type'           => 'text',
                'width'          => '115px',
                'filter'         => false,
                'sortable'       => false,
                'frame_callback' => array($this, 'callbackColumnAction'),
            ));
        }

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnAction($value, $row, $column, $isExport)
    {
        $select = Mage::helper('M2ePro')->__('Select');

        return <<<HTML
<a href="javascript:void(0)"
onclick="WalmartTemplateSellingFormatHandlerObj.taxCodePopupSelectAndClose({$row->getData('tax_code')});">
{$select}
</a>
HTML;
    }

    //########################################

    protected function callbackFilterTaxCodes($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $this->getCollection()->addFilter(
            'tax_code', $value, Ess_M2ePro_Model_Collection_Custom::CONDITION_LIKE
        );
    }

    protected function callbackFilterDescription($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $this->getCollection()->addFilter(
            'description', $value, Ess_M2ePro_Model_Collection_Custom::CONDITION_LIKE
        );
    }

    //########################################

    public function getRowUrl($item)
    {
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/getTaxCodesGrid', array('_current' => true));
    }

    //########################################
}