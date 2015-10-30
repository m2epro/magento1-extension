<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Grid
    extends Ess_M2ePro_Block_Adminhtml_Common_Template_Grid
{
    const TEMPLATE_SHIPPING_OVERRIDE = 'shipping_override';
    const TEMPLATE_DESCRIPTION = 'description';

    protected $nick = Ess_M2ePro_Helper_Component_Amazon::NICK;

    private $enabledMarketplacesCollection = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('commonAmazonTemplateGrid');
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        // Prepare selling format collection
        // ---------------------------------------
        $collectionSellingFormat = Mage::getModel('M2ePro/Template_SellingFormat')->getCollection();
        $collectionSellingFormat->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionSellingFormat->getSelect()->columns(
            array(
                'id as template_id',
                'title',
                new Zend_Db_Expr('\''.self::TEMPLATE_SELLING_FORMAT.'\' as `type`'),
                new Zend_Db_Expr('\'0\' as `marketplace_id`'),
                'create_date',
                'update_date',
                new Zend_Db_Expr('NULL as `category_path`'),
                new Zend_Db_Expr('NULL as `browsenode_id`'),
                new Zend_Db_Expr('NULL as `is_new_asin_accepted`')
            )
        );
        $collectionSellingFormat->getSelect()->where('component_mode = (?)', $this->nick);
        // ---------------------------------------

        // Prepare synchronization collection
        // ---------------------------------------
        $collectionSynchronization = Mage::getModel('M2ePro/Template_Synchronization')->getCollection();
        $collectionSynchronization->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionSynchronization->getSelect()->columns(
            array(
                'id as template_id',
                'title',
                new Zend_Db_Expr('\''.self::TEMPLATE_SYNCHRONIZATION.'\' as `type`'),
                new Zend_Db_Expr('\'0\' as `marketplace_id`'),
                'create_date',
                'update_date',
                new Zend_Db_Expr('NULL as `category_path`'),
                new Zend_Db_Expr('NULL as `browsenode_id`'),
                new Zend_Db_Expr('NULL as `is_new_asin_accepted`')
            )
        );
        $collectionSynchronization->getSelect()->where('component_mode = (?)', $this->nick);
        // ---------------------------------------

        // Prepare shipping override collection
        // ---------------------------------------
        $collectionShippingOverride = Mage::getModel('M2ePro/Amazon_Template_ShippingOverride')->getCollection();
        $collectionShippingOverride->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionShippingOverride->getSelect()->columns(
            array(
                'id as template_id',
                'title',
                new Zend_Db_Expr('\''.self::TEMPLATE_SHIPPING_OVERRIDE.'\' as `type`'),
                'marketplace_id',
                'create_date',
                'update_date',
                new Zend_Db_Expr('NULL as `category_path`'),
                new Zend_Db_Expr('NULL as `browsenode_id`'),
                new Zend_Db_Expr('NULL as `is_new_asin_accepted`')
            )
        );
        // ---------------------------------------

        // Prepare shipping override collection
        // ---------------------------------------
        $collectionDescription = Mage::helper('M2ePro/Component_Amazon')->getCollection('Template_Description');

        $collectionDescription->getSelect()->join(
            array('mm' => Mage::getModel('M2ePro/Marketplace')->getResource()->getMainTable()),
            'mm.id=second_table.marketplace_id',
            array()
        );

        $collectionDescription->addFieldToFilter('mm.status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);

        $collectionDescription->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionDescription->getSelect()->columns(
            array(
                'id as template_id',
                'title',
                new Zend_Db_Expr('\''.self::TEMPLATE_DESCRIPTION.'\' as `type`'),
                'second_table.marketplace_id',
                'create_date',
                'update_date',
                'second_table.category_path',
                'second_table.browsenode_id',
                'second_table.is_new_asin_accepted'
            )
        );
        // ---------------------------------------

        // Prepare union select
        // ---------------------------------------
        $unionSelect = $connRead->select();
        $unionSelect->union(array(
            $collectionSellingFormat->getSelect(),
            $collectionSynchronization->getSelect(),
            $collectionDescription->getSelect(),
            $collectionShippingOverride->getSelect()
        ));
        // ---------------------------------------

        // Prepare result collection
        // ---------------------------------------
        $resultCollection = new Varien_Data_Collection_Db($connRead);
        $resultCollection->getSelect()->reset()->from(
            array('main_table' => $unionSelect),
            array(
                'template_id',
                'title',
                'type',
                'marketplace_id',
                'create_date',
                'update_date',
                'category_path',
                'browsenode_id',
                'is_new_asin_accepted'
            )
        );
        // ---------------------------------------

//        echo $resultCollection->getSelectSql(true); exit;

        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    //########################################

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
            self::TEMPLATE_DESCRIPTION => Mage::helper('M2ePro')->__('Description'),
            self::TEMPLATE_SYNCHRONIZATION => Mage::helper('M2ePro')->__('Synchronization')
        );

        $this->getColumn('type')->setData('options', $options);

        $this->getColumn('title')->setData('header', Mage::helper('M2ePro')->__('Title / Description Policy Category'));
        $this->getColumn('title')->setData('frame_callback', array($this, 'callbackColumnTitle'));
        $this->getColumn('title')->setData('filter_condition_callback', array($this, 'callbackFilterTitle'));

        return $this;
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        if ($row->getData('type') != self::TEMPLATE_DESCRIPTION) {
            return $value;
        }

        $titleWord = Mage::helper('M2ePro')->__('Title');
        $title = Mage::helper('M2ePro')->escapeHtml($value);

        $categoryWord = Mage::helper('M2ePro')->__('Category');
        $categoryPath = !empty($row['category_path']) ? "{$row['category_path']} ({$row['browsenode_id']})"
            : Mage::helper('M2ePro')->__('Not Set');

        $newAsin = Mage::helper('M2ePro')->__('New ASIN/ISBN');
        $newAsinAccepted = Mage::helper('M2ePro')->__('No');
        if ($row->getData('is_new_asin_accepted') == 1) {
            $newAsinAccepted = Mage::helper('M2ePro')->__('Yes');
        }

        return <<<HTML
{$title}
<div>
    <span style="font-weight: bold">{$newAsin}</span>: <span style="color: #505050">{$newAsinAccepted}</span><br/>
    <span style="font-weight: bold">{$categoryWord}</span>: <span style="color: #505050">{$categoryPath}</span><br/>
</div>
HTML;
    }

    public function callbackColumnMarketplace($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return Mage::helper('M2ePro')->__('Any');
        }

        return $value;
    }

    //########################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'category_path LIKE ? OR browsenode_id LIKE ? OR title LIKE ?',
            '%'. $value .'%'
        );
    }

    protected function callbackFilterMarketplace($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('marketplace_id = 0 OR marketplace_id = ?', (int)$value);
    }

    //########################################

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

    //########################################

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

    //########################################
}