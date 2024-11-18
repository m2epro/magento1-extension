<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_ProductType_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType */
    private $dictionaryProductTypeResource;
    /** @var Ess_M2ePro_Model_Resource_Marketplace */
    private $marketplaceResource;
    /** @var Ess_M2ePro_Model_Amazon_Marketplace_Repository */
    private $marketplaceRepository;

    public function __construct($attributes = array())
    {
        $this->dictionaryProductTypeResource = Mage::getResourceModel('M2ePro/Amazon_Dictionary_ProductType');
        $this->marketplaceResource = Mage::getResourceModel('M2ePro/Marketplace');
        $this->marketplaceRepository = Mage::getModel('M2ePro/Amazon_Marketplace_Repository');

        parent::__construct($attributes);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('amazonProductTypeGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $this->setCollection($this->prepareAndGetCollection());

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
        $collection = $this->prepareAndGetCollection();
        $options = array();
        foreach ($collection->getItems() as $item) {
            $marketplace = $this->marketplaceRepository->get($item->getMarketplaceId());
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
            '*/adminhtml_amazon_productTypes/edit',
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
                    'base' => '*/adminhtml_amazon_productTypes/edit',
                ),
                'field' => 'id',
            ),
            array(
                'caption' => Mage::helper('M2ePro')->__('Delete'),
                'class' => 'action-default scalable add primary',
                'url' => array(
                    'base' => '*/adminhtml_amazon_productTypes/delete',
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
        $isOutOfDate = $this->isOutOfDate($row);

        if (empty($value)) {
            $value = $dictionary->getTitle();
        }

        if ($isOutOfDate) {
            $value  .= ' ' . <<<HTML
<span style="color: orange">(Out Of Date)</span>
HTML;
        }

        if ($isInvalid) {
            $message = Mage::helper('M2ePro')->__(
                'This Product Type is no longer supported by Amazon. '
                . 'Please assign another Product Type to the products that use it.'
            );

            $value = <<<HTML
    $value 
    <br>
<span style="color: red">
    $message
</span>
HTML;
        }

        return $value;
    }

    private function prepareAndGetCollection()
    {
        $collection = Mage::getResourceModel('M2ePro/Amazon_Template_ProductType_Collection');

        $collection->getSelect()->join(
            array('adpt' => $this->dictionaryProductTypeResource->getMainTable()),
            'adpt.id = main_table.dictionary_product_type_id',
            array(
                'product_type_title' => 'adpt.' . Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_TITLE,
                'client_update_date' => 'adpt.' . Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::
                                                                                 COLUMN_CLIENT_DETAILS_LAST_UPDATE_DATE,
                'server_update_date' => 'adpt.' . Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::
                    COLUMN_SERVER_DETAILS_LAST_UPDATE_DATE
            )
        );

        $collection->getSelect()->join(
            array('m' => $this->marketplaceResource->getMainTable()),
            'm.id = adpt.marketplace_id AND m.status = 1',
            array('marketplace_title' => 'm.title')
        );

        return $collection;
    }

    private function isOutOfDate($row)
    {
        $result = false;

        if (
            isset($row['client_update_date'])
            && isset($row['server_update_date'])
            && ($row['server_update_date'] > $row['client_update_date'])
        ) {
            $result = true;
        }

        return $result;
    }
}