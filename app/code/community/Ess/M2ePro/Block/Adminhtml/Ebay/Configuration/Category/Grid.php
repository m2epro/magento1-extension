<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_Category as Template;

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayConfigurationCategoryGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var Ess_M2ePro_Model_Resource_Collection_Abstract $collection */
        $collection = Mage::getModel('M2ePro/Ebay_Template_Category')->getCollection();
        $collection->addFieldToFilter('category_mode', array('neq' => Template::CATEGORY_MODE_NONE));
        $collection->addFieldToFilter('is_custom_template', 0);

        $collection->getSelect()->group(
            array(
                'main_table.category_mode',
                'main_table.category_id',
                'main_table.category_attribute',
                'main_table.marketplace_id'
            )
        );

        $collection->getSelect()->joinLeft(
            array(
                'edc' => Mage::helper('M2ePro/Module_Database_Structure')
                                    ->getTableNameWithPrefix('m2epro_ebay_dictionary_category')
            ),
            'edc.category_id = main_table.category_id AND edc.marketplace_id = main_table.marketplace_id',
            array(
                'state' => new Zend_Db_Expr('IF(edc.category_id, 1, 0)')
            )
        );

        //----------------------------------------

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $selectPrimary = $connRead
            ->select()
            ->from(
                array('elp' => Mage::getModel('M2ePro/Ebay_Listing_Product')->getResource()->getMainTable()),
                array(new Zend_Db_Expr('COUNT(listing_product_id)'))
            )
            ->joinLeft(
                array(
                    'etc1' => Mage::getModel('M2ePro/Ebay_Template_Category')->getResource()->getMainTable()
                ),
                'elp.template_category_id = etc1.id',
                array()
            )
            ->where(
                str_replace(
                    array('%ali%', '%ebay_mode%'),
                    array('etc1', Template::CATEGORY_MODE_EBAY),
                    'IF(%ali%.category_mode = %ebay_mode%, %ali%.category_id, %ali%.category_attribute) = IF(
                        main_table.category_mode = %ebay_mode%, main_table.category_id, main_table.category_attribute
                    )'
                )
            )
            ->where('main_table.marketplace_id = etc1.marketplace_id');

        $selectSecondary = $connRead
            ->select()
            ->from(
                array('elp2' => Mage::getModel('M2ePro/Ebay_Listing_Product')->getResource()->getMainTable()),
                array(new Zend_Db_Expr('COUNT(listing_product_id)'))
            )
            ->joinLeft(
                array(
                    'etc2' => Mage::getModel('M2ePro/Ebay_Template_Category')->getResource()->getMainTable()
                ),
                'elp2.template_category_secondary_id = etc2.id',
                array()
            )
            ->where(
                str_replace(
                    array('%ali%', '%ebay_mode%'),
                    array('etc2', Template::CATEGORY_MODE_EBAY),
                    'IF(%ali%.category_mode = %ebay_mode%, %ali%.category_id, %ali%.category_attribute) = IF(
                        main_table.category_mode = %ebay_mode%, main_table.category_id, main_table.category_attribute
                    )'
                )
            )
            ->where('main_table.marketplace_id = etc2.marketplace_id');

        $collection->getSelect()->columns(
            array(
                'template_category_id_count'           => $selectPrimary,
                'template_category_secondary_id_count' => $selectSecondary
            )
        );

        //----------------------------------------

        $selectSpecificsTotal = $connRead
            ->select()
            ->from(
                Mage::getModel('M2ePro/Ebay_Template_Category_Specific')->getResource()->getMainTable(),
                array(new Zend_Db_Expr('COUNT(id)'))
            )
            ->where('template_category_id = main_table.id');

        $collection->getSelect()->columns(
            array('template_category_specifics_count' => $selectSpecificsTotal)
        );

        $selectSpecificsUsed = $connRead
            ->select()
            ->from(
                Mage::getModel('M2ePro/Ebay_Template_Category_Specific')->getResource()->getMainTable(),
                array(new Zend_Db_Expr('COUNT(id)'))
            )
            ->where('template_category_id = main_table.id')
            ->where('value_mode != ?', Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE);

        $collection->getSelect()->columns(
            array('template_category_specifics_used_count' => $selectSpecificsUsed)
        );

        //----------------------------------------

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'path', array(
                'header'        => Mage::helper('M2ePro')->__('Title'),
                'align'         => 'left',
                'type'          => 'text',
                'escape'        => true,
                'index'         => 'main_table.category_path',
                'frame_callback' => array($this, 'callbackColumnPath'),
                'filter_condition_callback' => array($this, 'callbackFilterPath'),
            )
        );

        $this->addColumn(
            'marketplace', array(
                'header'        => Mage::helper('M2ePro')->__('Marketplace'),
                'align'         => 'left',
                'type'          => 'options',
                'width'         => '150px',
                'index'         => 'marketplace_id',
                'filter_index'  => 'main_table.marketplace_id',
                'options'       => Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace')
                                        ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
                                        ->setOrder('sorder', 'ASC')
                                        ->toOptionHash()
                )
        );

        $this->addColumn(
            'products_primary', array(
                'header' => Mage::helper('M2ePro')->__('Products: Primary'),
                'align'  => 'left',
                'type'   => 'test',
                'width'  => '100px',
                'index'  => 'template_category_id_count',
                'filter' => false,
            )
        );

        $this->addColumn(
            'products_secondary', array(
                'header' => Mage::helper('M2ePro')->__('Products: Secondary'),
                'align'  => 'left',
                'type'   => 'test',
                'width'  => '100px',
                'index'  => 'template_category_secondary_id_count',
                'filter' => false,
            )
        );

        $this->addColumn(
            'specifics_total', array(
                'header' => Mage::helper('M2ePro')->__('Specifics: Total'),
                'align'  => 'left',
                'type'   => 'test',
                'width'  => '100px',
                'index'  => 'template_category_specifics_count',
                'filter' => false,
            )
        );

        $this->addColumn(
            'specifics_used', array(
                'header' => Mage::helper('M2ePro')->__('Specifics: Used'),
                'align'  => 'left',
                'type'   => 'test',
                'width'  => '100px',
                'index'  => 'template_category_specifics_used_count',
                'filter' => false,
            )
        );

        $this->addColumn(
            'state', array(
                'header'        => Mage::helper('M2ePro')->__('State'),
                'align'         => 'left',
                'type'          => 'options',
                'index'         => 'state',
                'width'         => '150px',
                'sortable'      => false,
                'filter_condition_callback' => array($this, 'callbackFilterState'),
                'frame_callback'=> array($this, 'callbackColumnState'),
                'options'       => array(
                    1 => Mage::helper('M2ePro')->__('Active'),
                    0 => Mage::helper('M2ePro')->__('Removed'),
                ),
            )
        );

        $this->addColumn(
            'actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '70px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
            'getter'    => 'getTemplateId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('M2ePro')->__('View'),
                    'url'       => array(
                        'base' => '*/adminhtml_ebay_category/view',
                        'params' => array(
                            'template_id' => '$id',
                        )
                    ),
                    'field' => 'id'
                ),
            )
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem(
            'delete', array(
                'label'    => Mage::helper('M2ePro')->__('Remove'),
                'url'      => $this->getUrl('*/adminhtml_ebay_category/delete'),
                'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            )
        );

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnPath($value, $row, $column, $isExport)
    {
        $mode = $row->getData('category_mode');
        $value = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
            $row->getData('category_id'), $row->getData('marketplace_id')
        );
        $value .= ' (' . $row->getData('category_id') . ')';

        if ($mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            $value = Mage::helper('M2ePro')->__('Magento Attribute') .' > '.
                     Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($row->getData('category_attribute'));
        }

        return $value;
    }

    public function callbackColumnState($value, $row, $column, $isExport)
    {
        if ($row->getData('category_mode') == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            $row->setData('state', 1);
        }

        return $column->getRenderer()->render($row);
    }

    //########################################

    protected function callbackFilterPath($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'main_table.category_path LIKE ? OR main_table.category_id LIKE ? OR main_table.category_attribute LIKE ?',
            '%'. $value . '%'
        );
    }

    protected function callbackFilterState($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        if ($value == 1) {
            $collection->getSelect()->where(
                '(edc.category_id IS NOT NULL) OR
                (main_table.category_mode = '.Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE.')'
            );
        } else {
            $collection->getSelect()->where(
                '(edc.category_id IS NULL) AND
                (main_table.category_mode != '.Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE.')'
            );
        }
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getRowClass($row)
    {
        if ($row->getData('category_mode') == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            return '';
        }

        return $row->getData('state') ? '' : 'invalid-row';
    }

    //########################################
}
