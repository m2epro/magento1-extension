<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    private $enabledMarketplacesCollection = NULL;

    // ##########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateGrid');
        //------------------------------

        // Set default values
        //------------------------------
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
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
        $collectionSellingFormat->getSelect()->join(
            array('etsf' => Mage::getModel('M2ePro/Ebay_Template_SellingFormat')->getResource()->getMainTable()),
            'main_table.id=etsf.template_selling_format_id',
            array('is_custom_template')
        );
        $collectionSellingFormat->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionSellingFormat->getSelect()->columns(
            array('id as template_id', 'title', new Zend_Db_Expr('\'0\' as `marketplace`'),
                  new Zend_Db_Expr('\''.Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT.'\' as `nick`'),
                  'create_date', 'update_date')
        );
        $collectionSellingFormat->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);
        $collectionSellingFormat->addFieldToFilter('is_custom_template', 0);
        // ----------------------------------

        // Prepare synchronization collection
        // ----------------------------------
        $collectionSynchronization = Mage::getModel('M2ePro/Template_Synchronization')->getCollection();
        $collectionSynchronization->getSelect()->join(
            array('ets' => Mage::getModel('M2ePro/Ebay_Template_Synchronization')->getResource()->getMainTable()),
            'main_table.id=ets.template_synchronization_id',
            array('is_custom_template')
        );
        $collectionSynchronization->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionSynchronization->getSelect()->columns(
            array('id as template_id', 'title', new Zend_Db_Expr('\'0\' as `marketplace`'),
                new Zend_Db_Expr('\''.Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION.'\' as `nick`'),
                'create_date', 'update_date')
        );
        $collectionSynchronization->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);
        $collectionSynchronization->addFieldToFilter('is_custom_template', 0);
        // ----------------------------------

        // Prepare description collection
        // ----------------------------------
        $collectionDescription = Mage::getModel('M2ePro/Template_Description')->getCollection();
        $collectionDescription->getSelect()->join(
            array('ets' => Mage::getModel('M2ePro/Ebay_Template_Description')->getResource()->getMainTable()),
            'main_table.id=ets.template_description_id',
            array('is_custom_template')
        );
        $collectionDescription->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionDescription->getSelect()->columns(
            array('id as template_id', 'title', new Zend_Db_Expr('\'0\' as `marketplace`'),
                new Zend_Db_Expr('\''.Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION.'\' as `nick`'),
                'create_date', 'update_date')
        );
        $collectionDescription->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);
        $collectionDescription->addFieldToFilter('is_custom_template', 0);
        // ----------------------------------

        // Prepare payment collection
        // ----------------------------------
        $collectionPayment = Mage::getModel('M2ePro/Ebay_Template_Payment')->getCollection();
        $collectionPayment->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionPayment->getSelect()->columns(
            array('id as template_id', 'title', 'marketplace_id as marketplace',
                new Zend_Db_Expr('\''.Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT.'\' as `nick`'),
                'create_date', 'update_date')
        );
        $collectionPayment->addFieldToFilter('is_custom_template', 0);
        $collectionPayment->addFieldToFilter('marketplace_id', array('in' => $this->getEnabledMarketplacesIds()));
        // ----------------------------------

        // Prepare shipping collection
        // ----------------------------------
        $collectionShipping = Mage::getModel('M2ePro/Ebay_Template_Shipping')->getCollection();
        $collectionShipping->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionShipping->getSelect()->columns(
            array('id as template_id', 'title', 'marketplace_id as marketplace',
                new Zend_Db_Expr('\''.Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING.'\' as `nick`'),
                'create_date', 'update_date')
        );
        $collectionShipping->addFieldToFilter('is_custom_template', 0);
        $collectionShipping->addFieldToFilter('marketplace_id', array('in' => $this->getEnabledMarketplacesIds()));
        // ----------------------------------

        // Prepare return collection
        // ----------------------------------
        $collectionReturn = Mage::getModel('M2ePro/Ebay_Template_Return')->getCollection();
        $collectionReturn->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionReturn->getSelect()->columns(
            array('id as template_id', 'title', 'marketplace_id as marketplace',
                new Zend_Db_Expr('\''.Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN.'\' as `nick`'),
                'create_date', 'update_date')
        );
        $collectionReturn->addFieldToFilter('is_custom_template', 0);
        $collectionReturn->addFieldToFilter('marketplace_id', array('in' => $this->getEnabledMarketplacesIds()));
        // ----------------------------------

        // Prepare union select
        // ----------------------------------
        $unionSelect = $connRead->select();
        $unionSelect->union(array(
            $collectionSellingFormat->getSelect(),
            $collectionSynchronization->getSelect(),
            $collectionDescription->getSelect(),
            $collectionPayment->getSelect(),
            $collectionShipping->getSelect(),
            $collectionReturn->getSelect()
        ));
        // ----------------------------------

        // Prepare result collection
        // ----------------------------------
        $resultCollection = new Varien_Data_Collection_Db($connRead);
        $resultCollection->getSelect()->reset()->from(
            array('main_table' => $unionSelect),
            array('template_id', 'title', 'nick', 'marketplace', 'create_date', 'update_date')
        );
        // ----------------------------------

//        var_dump($resultCollection->getSelectSql(true)); exit;

        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header'        => Mage::helper('M2ePro')->__('Title'),
            'align'         => 'left',
            'type'          => 'text',
//            'width'         => '150px',
            'index'         => 'title',
            'escape'        => true,
            'filter_index'  => 'main_table.title'
        ));

        $options = array(
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT => Mage::helper('M2ePro')->__('Payment'),
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING => Mage::helper('M2ePro')->__('Shipping'),
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN => Mage::helper('M2ePro')->__('Return'),
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT
                => Mage::helper('M2ePro')->__('Price, Quantity and Format'),
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION
                => Mage::helper('M2ePro')->__('Description'),
            Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION
                => Mage::helper('M2ePro')->__('Synchronization')
        );
        $this->addColumn('nick', array(
            'header'        => Mage::helper('M2ePro')->__('Type'),
            'align'         => 'left',
            'type'          => 'options',
            'width'         => '100px',
            'sortable'      => false,
            'index'         => 'nick',
            'filter_index'  => 'main_table.nick',
            'options'       => $options
        ));

        $this->addColumn('marketplace', array(
            'header'        => Mage::helper('M2ePro')->__('eBay Site'),
            'align'         => 'left',
            'type'          => 'options',
            'width'         => '100px',
            'index'         => 'marketplace',
            'filter_index'  => 'main_table.marketplace',
            'filter_condition_callback' => array($this, 'callbackFilterMarketplace'),
            'frame_callback'=> array($this, 'callbackColumnMarketplace'),
            'options'       => $this->getEnabledMarketplaceTitles()
        ));

        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date'
        ));

        $this->addColumn('update_date', array(
            'header'    => Mage::helper('M2ePro')->__('Update Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'update_date',
            'filter_index' => 'main_table.update_date'
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getTemplateId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit'),
                    'url'       => array(
                        'base' => '*/adminhtml_ebay_template/edit',
                        'params' => array('nick' => '$nick')
                    ),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Delete'),
                    'url'       => array(
                        'base' => '*/adminhtml_ebay_template/delete',
                        'params' => array('nick' => '$nick')
                    ),
                    'field'     => 'id',
                    'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
                )
            )
        ));

        return parent::_prepareColumns();
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

        $collection->getSelect()->where('main_table.marketplace = 0 OR main_table.marketplace = ?', (int)$value);
    }

    // ##########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/templateGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/adminhtml_ebay_template/edit',
            array(
                'id' => $row->getData('template_id'),
                'nick' => $row->getData('nick'),
                'back' => 1
            )
        );
    }

    // ##########################################

    private function getEnabledMarketplacesCollection()
    {
        if (is_null($this->enabledMarketplacesCollection)) {
            $collection = Mage::getModel('M2ePro/Marketplace')->getCollection();
            $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);
            $collection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
            $collection->setOrder('sorder', 'ASC');

            $this->enabledMarketplacesCollection = $collection;
        }

        return $this->enabledMarketplacesCollection;
    }

    private function getEnabledMarketplacesIds()
    {
        return $this->getEnabledMarketplacesCollection()->getAllIds();
    }

    private function getEnabledMarketplaceTitles()
    {
        return $this->getEnabledMarketplacesCollection()->toOptionHash();
    }

    // ##########################################
}