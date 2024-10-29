<?php

class Ess_M2ePro_Block_Adminhtml_Walmart_ProductType_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var Ess_M2ePro_Model_Resource_Walmart_ProductType_CollectionFactory */
    private $productTypeCollectionFactory;
    /** @var Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType */
    private $dictionaryProductTypeResource;
    /** @var Ess_M2ePro_Model_Resource_Marketplace */
    private $marketplaceResource;
    /** @var Ess_M2ePro_Model_Walmart_Marketplace_Repository */
    private $marketplaceRepository;

    public function __construct($attributes = array())
    {
        $this->productTypeCollectionFactory = Mage::getResourceModel('M2ePro/Walmart_ProductType_CollectionFactory');
        $this->dictionaryProductTypeResource = Mage::getResourceModel('M2ePro/Walmart_Dictionary_ProductType');
        $this->marketplaceResource = Mage::getResourceModel('M2ePro/Marketplace');
        $this->marketplaceRepository = Mage::getModel('M2ePro/Walmart_Marketplace_Repository');

        parent::__construct($attributes);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('walmartProductTypeGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->productTypeCollectionFactory->create();

        $collection->getSelect()->join(
            array('adpt' => $this->dictionaryProductTypeResource->getMainTable()),
            'adpt.id = main_table.dictionary_product_type_id',
            array('product_type_title' => 'adpt.title')
        );

        $collection->getSelect()->join(
            array('m' => $this->marketplaceResource->getMainTable()),
            'm.id = adpt.marketplace_id AND m.status = 1',
            array('marketplace_title' => 'm.title')
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title',
            array(
                'header' => Mage::helper('M2ePro')->__('Title'),
                'align' => 'left',
                'type' => 'text',
                'index' => 'title',
                'escape' => true,
                'filter_index' => 'main_table.title',
                'frame_callback' => array($this, 'callbackColumnTitle'),
            )
        );

        $this->addColumn(
            'marketplace',
            array(
                'header' => Mage::helper('M2ePro')->__('Marketplace'),
                'align' => 'left',
                'type' => 'options',
                'width' => '100px',
                'index' => 'marketplace_title',
                'filter_condition_callback' => array($this, 'callbackFilterMarketplace'),
                'options' => $this->getEnabledMarketplaceOptions(),
            )
        );

        $this->addColumn(
            'create_date',
            array(
                'header' => Mage::helper('M2ePro')->__('Creation Date'),
                'align' => 'left',
                'width' => '150px',
                'type' => 'datetime',
                'filter_time' => true,
                'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'index' => 'create_date',
                'filter_index' => 'main_table.create_date',
            )
        );

        $this->addColumn(
            'update_date',
            array(
                'header' => Mage::helper('M2ePro')->__('Update Date'),
                'align' => 'left',
                'width' => '150px',
                'type' => 'datetime',
                'filter_time' => true,
                'format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'index' => 'update_date',
                'filter_index' => 'main_table.update_date',
            )
        );

        $this->addColumn(
            'actions',
            array(
                'header' => Mage::helper('M2ePro')->__('Actions'),
                'align' => 'left',
                'width' => '100px',
                'type' => 'action',
                'index' => 'actions',
                'filter' => false,
                'sortable' => false,
                'getter' => 'getId',
                'actions' => $this->getRowActions(),
            )
        );

        return parent::_prepareColumns();
    }

    protected function callbackFilterMarketplace($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $this->getCollection()->getSelect()->where('adpt.marketplace_id = ?', $value);
    }

    /**
     * @return array
     */
    private function getEnabledMarketplaceOptions()
    {
        $options = array();
        foreach ($this->marketplaceRepository->findActive() as $marketplace) {
            $options[$marketplace->getId()] = $marketplace->getTitle();
        }

        return $options;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($item)
    {
        return $this->getUrl(
            '*/adminhtml_walmart_productType/edit',
            array(
                'id' => $item->getData('id'),
                'back' => 1,
            )
        );
    }

    private function getRowActions()
    {
        return array(
            array(
                'caption' => Mage::helper('M2ePro')->__('Edit'),
                'url' => array(
                    'base' => '*/adminhtml_walmart_productType/edit',
                ),
                'field' => 'id',
            ),
            array(
                'caption' => Mage::helper('M2ePro')->__('Delete'),
                'class' => 'action-default scalable add primary',
                'url' => array(
                    'base' => '*/adminhtml_walmart_productType/delete',
                ),
                'field' => 'id',
                'confirm' => Mage::helper('M2ePro')->__('Are you sure?'),
            ),
        );
    }

    /**
     * @param Ess_M2ePro_Model_Walmart_ProductType $row
     */
    public function callbackColumnTitle($value, $row)
    {
        $dictionary = $row->getDictionary();
        $isInvalid = $dictionary->isInvalid();

        if (empty($value)) {
            $value = $dictionary->getTitle();
        }

        if ($isInvalid) {
            $tooltipHtml = $this->getTooltipHtml(
                $this->getSkinUrl('M2ePro/images/warning.png'),
                Mage::helper('M2ePro')->__(
                    'This Product Type is no longer supported by Walmart. '
                    . 'Please assign another Product Type to the products that use it.'
                )
            );

            $value .= '&nbsp;' . $tooltipHtml;
        }

        return $value;
    }

    private function getTooltipHtml($icon, $content)
    {
        return <<<TOOLTIP
<span>
    <img class="tool-tip-image" style="vertical-align:middle; height:14px" src="$icon">
    <span class="tool-tip-message tip-right" style="display:none;text-align: left;width: 300px;">
        <span style="color:gray;">$content</span>
    </span>
</span>
TOOLTIP;
    }
}
