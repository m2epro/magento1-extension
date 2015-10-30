<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View_Group_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $listingProductId;
    private $listingProduct;

    private $motorsType;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayMotorViewGroupGrid');

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

    //------------------------------

    protected function _prepareCollection()
    {
        $motorsHelper = Mage::helper('M2ePro/Component_Ebay_Motors');

        $attributeValue = $this->getListingProduct()->getMagentoProduct()->getAttributeValue(
            $motorsHelper->getAttribute($this->getMotorsType())
        );

        $motorsData = $motorsHelper->parseAttributeValue($attributeValue);

        /** @var Ess_M2ePro_Model_Mysql4_Ebay_Motor_Group_Collection $collection */
        $collection = Mage::getModel('M2ePro/Ebay_Motor_Group')->getCollection();
        $collection->getSelect()->where('id IN (?)', $motorsData['groups']);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header'       => Mage::helper('M2ePro')->__('Title'),
            'align'        => 'left',
            'type'         => 'text',
            'index'        => 'title',
            'filter_index' => 'title',
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setUseSelectAll(false);
        $this->setMassactionIdFieldOnlyIndexValue(true);

        // Set mass-action
        //--------------------------------
        $this->getMassactionBlock()->addItem('removeGroup', array(
            'label'   => Mage::helper('M2ePro')->__('Remove'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
        ));
        //--------------------------------

        return parent::_prepareMassaction();
    }

    public function getMassactionBlockName()
    {
        // this is required for correct work of massaction js
        return 'M2ePro/adminhtml_grid_massaction';
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        return $value;
    }

    //########################################

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
EbayMotorViewGroupGridHandlerObj = new EbayMotorViewGroupGridHandler(
    '{$this->getId()}',
    '{$this->getListingProductId()}'
);
JS;
        }

        $additionalHtml .= <<<JS
EbayMotorViewGroupGridHandlerObj.afterInitPage();
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
        return $this->getUrl('*/adminhtml_ebay_motor/viewGroupGrid', array(
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
            return Mage::helper('M2ePro')->__('ePID(s)');
        }

        return Mage::helper('M2ePro')->__('kType(s)');
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

    public function getListingProduct()
    {
        if (is_null($this->listingProduct)) {
            $this->listingProduct = Mage::helper('M2ePro/Component_Ebay')
                ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->listingProduct;
    }

    //########################################
}