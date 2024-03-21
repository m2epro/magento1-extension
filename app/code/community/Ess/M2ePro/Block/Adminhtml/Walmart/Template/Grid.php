<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Template_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    const TEMPLATE_SELLING_FORMAT    = 'selling_format';
    const TEMPLATE_CATEGORY          = 'category';
    const TEMPLATE_SYNCHRONIZATION   = 'synchronization';
    const TEMPLATE_DESCRIPTION       = 'description';

    protected $_enabledMarketplacesCollection = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartTemplateGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $marketplaceMainTable = Mage::getModel('M2ePro/Marketplace')->getResource()->getMainTable();

        // Prepare category collection
        // ---------------------------------------
        $collectionCategory = Mage::getModel('M2ePro/Walmart_Template_Category')->getCollection();
        $collectionCategory->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionCategory->getSelect()->join(
            array('mm' => $marketplaceMainTable),
            'main_table.marketplace_id=mm.id',
            array()
        );
        $collectionCategory->getSelect()->columns(
            array(
                'id as template_id',
                'title',
                new Zend_Db_Expr('\''.self::TEMPLATE_CATEGORY.'\' as `type`'),
                new Zend_Db_Expr('mm.title as `marketplace_title`'),
                new Zend_Db_Expr('mm.id as `marketplace_id`'),
                'create_date',
                'update_date',
                'category_path',
                'browsenode_id'
            )
        );
        // ---------------------------------------

        // Prepare selling format collection
        // ---------------------------------------
        $collectionSellingFormat = Mage::helper('M2ePro/Component_Walmart')->getCollection('Template_SellingFormat');
        $collectionSellingFormat->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionSellingFormat->getSelect()->join(
            array('mm2' => $marketplaceMainTable),
            'second_table.marketplace_id=mm2.id',
            array()
        );
        $collectionSellingFormat->getSelect()->columns(
            array(
                'id as template_id',
                'title',
                new Zend_Db_Expr('\''.self::TEMPLATE_SELLING_FORMAT.'\' as `type`'),
                new Zend_Db_Expr('mm2.title as `marketplace_title`'),
                new Zend_Db_Expr('mm2.id as `marketplace_id`'),
                'create_date',
                'update_date',
                new Zend_Db_Expr('NULL as `category_path`'),
                new Zend_Db_Expr('NULL as `browsenode_id`')
            )
        );
        $collectionSellingFormat->getSelect()->where('main_table.component_mode = (?)', Ess_M2ePro_Helper_Component_Walmart::NICK);
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
                new Zend_Db_Expr('NULL as `marketplace_title`'),
                new Zend_Db_Expr('\'0\' as `marketplace_id`'),
                'create_date',
                'update_date',
                new Zend_Db_Expr('NULL as `category_path`'),
                new Zend_Db_Expr('NULL as `browsenode_id`')
            )
        );
        $collectionSynchronization->getSelect()->where(
            'component_mode = (?)', Ess_M2ePro_Helper_Component_Walmart::NICK
        );
        // ---------------------------------------

        // Prepare description collection
        // ---------------------------------------
        $collectionDescription = Mage::helper('M2ePro/Component_Walmart')->getCollection('Template_Description');

        $collectionDescription->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionDescription->getSelect()->columns(
            array(
                'id as template_id',
                'title',
                new Zend_Db_Expr('\''.self::TEMPLATE_DESCRIPTION.'\' as `type`'),
                new Zend_Db_Expr('NULL as `marketplace_title`'),
                new Zend_Db_Expr('\'0\' as `marketplace_id`'),
                'create_date',
                'update_date',
                new Zend_Db_Expr('NULL as `category_path`'),
                new Zend_Db_Expr('NULL as `browsenode_id`')
            )
        );
        // ---------------------------------------

        // Prepare union select
        // ---------------------------------------
        $collectionsArray = array(
            $collectionCategory->getSelect(),
            $collectionSellingFormat->getSelect(),
            $collectionSynchronization->getSelect(),
            $collectionDescription->getSelect()
        );

        $unionSelect = $connRead->select();
        $unionSelect->union($collectionsArray);
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
                'marketplace_title',
                'marketplace_id',
                'create_date',
                'update_date',
                'category_path',
                'browsenode_id'
            )
        );
        // ---------------------------------------

        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    //########################################

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title', array(
            'header'        => Mage::helper('M2ePro')->__('Details'),
            'align'         => 'left',
            'type'          => 'text',
            'index'         => 'title',
            'escape'        => true,
            'filter_index'  => 'main_table.title',
            'frame_callback' => array($this, 'callbackColumnTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $options = array(
            self::TEMPLATE_CATEGORY          => Mage::helper('M2ePro')->__('Category'),
            self::TEMPLATE_SELLING_FORMAT    => Mage::helper('M2ePro')->__('Selling'),
            self::TEMPLATE_DESCRIPTION       => Mage::helper('M2ePro')->__('Description'),
            self::TEMPLATE_SYNCHRONIZATION   => Mage::helper('M2ePro')->__('Synchronization')
        );
        $this->addColumn(
            'type', array(
            'header'        => Mage::helper('M2ePro')->__('Type'),
            'align'         => 'left',
            'type'          => 'options',
            'width'         => '120px',
            'sortable'      => false,
            'index'         => 'type',
            'filter_index'  => 'main_table.type',
            'options'       => $options
            )
        );

        $this->addColumn(
            'marketplace', array(
            'header'        => Mage::helper('M2ePro')->__('Marketplace'),
            'align'         => 'left',
            'type'          => 'options',
            'width'         => '100px',
            'index'         => 'marketplace_title',
            'filter_index'  => 'marketplace_title',
            'filter_condition_callback' => array($this, 'callbackFilterMarketplace'),
            'frame_callback'=> array($this, 'callbackColumnMarketplace'),
            'options'       => $this->getEnabledMarketplaceTitles()
            )
        );

        $this->addColumn(
            'create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date'
            )
        );

        $this->addColumn(
            'update_date', array(
            'header'    => Mage::helper('M2ePro')->__('Update Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'update_date',
            'filter_index' => 'main_table.update_date'
            )
        );

        $this->addColumn(
            'actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
            'getter'    => 'getTemplateId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit'),
                    'url'       => array(
                        'base' => '*/adminhtml_walmart_template/edit',
                        'params' => array(
                            'type'    => '$type'
                        )
                    ),
                    'field' => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Delete'),
                    'url'       => array(
                        'base' => '*/adminhtml_walmart_template/delete',
                        'params' => array(
                            'type'    => '$type'
                        )
                    ),
                    'field'   => 'id',
                    'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
                )
            )
            )
        );

        return $this;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        if ($row->getData('type') != self::TEMPLATE_CATEGORY) {
            return $value;
        }

        $title = Mage::helper('M2ePro')->escapeHtml($value);

        $categoryWord = Mage::helper('M2ePro')->__('Category');
        $categoryPath = !empty($row['category_path']) ? "{$row['category_path']} ({$row['browsenode_id']})"
            : Mage::helper('M2ePro')->__('Not Set');

        return <<<HTML
{$title}
<div>
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
            '*/adminhtml_walmart_template/edit',
            array(
                'id'   => $row->getData('template_id'),
                'type' => $row->getData('type'),
                'back' => 1
            )
        );
    }

    //########################################

    protected function getEnabledMarketplacesCollection()
    {
        if ($this->_enabledMarketplacesCollection === null) {
            $collection = Mage::getModel('M2ePro/Marketplace')->getCollection();
            $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Walmart::NICK);
            $collection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
            $collection->setOrder('sorder', 'ASC');

            $this->_enabledMarketplacesCollection = $collection;
        }

        return $this->_enabledMarketplacesCollection;
    }

    protected function getEnabledMarketplaceTitles()
    {
        return $this->getEnabledMarketplacesCollection()->toOptionHash();
    }

    //########################################
}
