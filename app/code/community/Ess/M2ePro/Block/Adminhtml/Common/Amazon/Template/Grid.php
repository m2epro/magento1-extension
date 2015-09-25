<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Grid
    extends Ess_M2ePro_Block_Adminhtml_Common_Template_Grid
{
    const TEMPLATE_SHIPPING_OVERRIDE = 'shipping_override';

    protected $nick = Ess_M2ePro_Helper_Component_Amazon::NICK;

    private $enabledMarketplacesCollection = NULL;

    // ##########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('commonAmazonTemplateGrid');
        //------------------------------
    }

    // ##########################################

    protected function _prepareCollection()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        // Prepare selling format collection
        // ----------------------------------
        $collectionSellingFormat = Mage::getModel('M2ePro/Template_SellingFormat')->getCollection();
        $collectionSellingFormat->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionSellingFormat->getSelect()->columns(
            array('id as template_id', 'title',
                new Zend_Db_Expr('\''.self::TEMPLATE_SELLING_FORMAT.'\' as `type`'),
                new Zend_Db_Expr('\'0\' as `marketplace_id`'),
                'create_date', 'update_date')
        );
        $collectionSellingFormat->getSelect()->where('component_mode = (?)', $this->nick);
        // ----------------------------------

        // Prepare synchronization collection
        // ----------------------------------
        $collectionSynchronization = Mage::getModel('M2ePro/Template_Synchronization')->getCollection();
        $collectionSynchronization->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionSynchronization->getSelect()->columns(
            array('id as template_id', 'title',
                new Zend_Db_Expr('\''.self::TEMPLATE_SYNCHRONIZATION.'\' as `type`'),
                new Zend_Db_Expr('\'0\' as `marketplace_id`'),
                'create_date', 'update_date')
        );
        $collectionSynchronization->getSelect()->where('component_mode = (?)', $this->nick);
        // ----------------------------------

        // Prepare shipping override collection
        // ----------------------------------
        $collectionShippingOverride = Mage::getModel('M2ePro/Amazon_Template_ShippingOverride')->getCollection();
        $collectionShippingOverride->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionShippingOverride->getSelect()->columns(
            array('id as template_id', 'title',
                new Zend_Db_Expr('\''.self::TEMPLATE_SHIPPING_OVERRIDE.'\' as `type`'),
                'marketplace_id', 'create_date', 'update_date')
        );
        // ----------------------------------

        // Prepare union select
        // ----------------------------------
        $unionSelect = $connRead->select();
        $unionSelect->union(array(
            $collectionSellingFormat->getSelect(),
            $collectionSynchronization->getSelect(),
            $collectionShippingOverride->getSelect()
        ));
        // ----------------------------------

        // Prepare result collection
        // ----------------------------------
        $resultCollection = new Varien_Data_Collection_Db($connRead);
        $resultCollection->getSelect()->reset()->from(
            array('main_table' => $unionSelect),
            array('template_id', 'title', 'type', 'marketplace_id', 'create_date', 'update_date')
        );
        // ----------------------------------

//        echo $resultCollection->getSelectSql(true); exit;

        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    // ##########################################

    protected function _prepareColumns()
    {
        $this->addColumnAfter('marketplace', array(
            'header'        => Mage::helper('M2ePro')->__('Marketplace'),
            'align'         => 'left',
            'type'          => 'options',
            'width'         => '100px',
            'index'         => 'marketplace_id',
            'filter_index'  => 'marketplace_id',
            'filter_condition_callback' => array($this, 'callbackFilterMarketplace'),
            'frame_callback'=> array($this, 'callbackColumnMarketplace'),
            'options'       => $this->getEnabledMarketplaceTitles()
        ), 'type');

        parent::_prepareColumns();

        $options = array(
            self::TEMPLATE_SELLING_FORMAT => Mage::helper('M2ePro')->__('Selling Format'),
            self::TEMPLATE_SHIPPING_OVERRIDE => Mage::helper('M2ePro')->__('Shipping Override'),
            self::TEMPLATE_SYNCHRONIZATION => Mage::helper('M2ePro')->__('Synchronization')
        );

        $this->getColumn('type')->setData('options', $options);

        return $this;
    }

    // ##########################################

    public function callbackColumnMarketplace($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return Mage::helper('M2ePro')->__('Any');
        }

        return $value;
    }

    protected function callbackFilterMarketplace($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('marketplace_id = 0 OR marketplace_id = ?', (int)$value);
    }

    // ##########################################

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/adminhtml_common_template/edit',
            array(
                'id' => $row->getData('template_id'),
                'type' => $row->getData('type'),
                'back' => 1
            )
        );
    }

    // ##########################################

    private function getEnabledMarketplacesCollection()
    {
        if (is_null($this->enabledMarketplacesCollection)) {
            $collection = Mage::getModel('M2ePro/Marketplace')->getCollection();
            $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK);
            $collection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
            $collection->setOrder('sorder', 'ASC');

            $this->enabledMarketplacesCollection = $collection;
        }

        return $this->enabledMarketplacesCollection;
    }

    private function getEnabledMarketplaceTitles()
    {
        return $this->getEnabledMarketplacesCollection()->toOptionHash();
    }

    // ##########################################
}