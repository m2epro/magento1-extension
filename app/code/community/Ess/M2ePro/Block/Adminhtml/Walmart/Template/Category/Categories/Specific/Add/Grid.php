<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Template_Category_Categories_Specific_Add_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public $marketplaceId;
    public $productDataNick;

    public $currentXpath;

    public $searchQuery;
    public $onlyDesired = false;

    public $selectedSpecifics = array();

    public $allRenderedSpecifics = array();
    public $blockRenderedSpecifics = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartTemplateDescriptionCategorySpecificAddGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $connRead->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')
                      ->getTableNameWithPrefix('m2epro_walmart_dictionary_specific')
            )
              ->where('marketplace_id = ?', (int)$this->marketplaceId)
              ->where('product_data_nick = ?', $this->productDataNick)

            ->where(
                'xml_tag NOT IN (?)',
                array(
                      Ess_M2ePro_Model_Walmart_Template_Category_Specific::UNIT_SPECIFIC_CODE,
                      Ess_M2ePro_Model_Walmart_Template_Category_Specific::MEASURE_SPECIFIC_CODE
                  )
            )

              ->where('xpath LIKE ?', "{$this->currentXpath}/%")
              ->order('title ASC');

        if ($this->searchQuery) {
            $select->where('title LIKE ? OR xml_tag LIKE ?', "%{$this->searchQuery}%");
        }

        $filteredResult = array();

        $queryStmt = $select->query();
        while ($row = $queryStmt->fetch()) {
            if (in_array($row['xpath'], $this->selectedSpecifics, true)) {
                continue;
            }

            if (in_array($row['xpath'], $this->allRenderedSpecifics, true)) {
                // an already rendered specific can be added again only to parent container directly
                if (str_replace($this->currentXpath . '/', '', $row['xpath']) !== $row['xml_tag']) {
                    continue;
                }

                if (in_array($row['xpath'], $this->blockRenderedSpecifics, true)) {
                    continue;
                }
            }

            $row['data_definition'] = (array)Mage::helper('M2ePro')->jsonDecode($row['data_definition']);
            $row['is_desired'] = !empty($row['data_definition']['is_desired']) && $row['data_definition']['is_desired'];

            if ($this->onlyDesired && !$row['is_desired']) {
                continue;
            }

            $filteredResult[] = $row;
        }

        usort(
            $filteredResult, function($a, $b) {

            if ($a['is_desired'] && !$b['is_desired']) {
                return -1;
            }

            if ($b['is_desired'] && !$a['is_desired']) {
                return 1;
            }

            return $a['title'] == $b['title'] ? 0 : ($a['title'] > $b['title'] ? 1 : -1);
            }
        );

        $collection = new Varien_Data_Collection();
        foreach ($filteredResult as $item) {
            $collection->addItem(new Varien_Object($item));
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title', array(
            'header'         => Mage::helper('M2ePro')->__('Specific'),
            'align'          => 'left',
            'type'           => 'text',
            'index'          => 'title',
            'width'          => '700px',
            'filter'         => false,
            'sortable'       => false,
            'frame_callback' => array($this, 'callbackColumnTitle')
            )
        );

        $this->addColumn(
            'is_desired', array(
            'header'         => Mage::helper('M2ePro')->__('Desired'),
            'align'          => 'center',
            'type'           => 'text',
            'index'          => 'is_desired',
            'width'          => '80px',
            'filter'         => false,
            'sortable'       => false,
            'frame_callback' => array($this, 'callbackColumnIsDesired')
            )
        );

        $this->addColumn(
            'actions', array(
            'header'         => Mage::helper('M2ePro')->__('Action'),
            'align'          => 'center',
            'type'           => 'text',
            'width'          => '80px',
            'filter'         => false,
            'sortable'       => false,
            'frame_callback' => array($this, 'callbackColumnActions'),
            )
        );
    }

    //########################################

    public function callbackColumnTitle($title, $row, $column, $isExport)
    {
        strlen($title) > 60 && $title = substr($title, 0, 60) . '...';
        $title = Mage::helper('M2ePro')->escapeHtml($title);

        $pathParts = explode('/', ltrim($row->getData('xpath'), '/'));
        array_walk(
            $pathParts, function (&$el) {
            $el = ucfirst(preg_replace('/(?<!^)[A-Z0-9]/', ' $0', $el));
            }
        );
        array_pop($pathParts);

        $path = implode(' > ', $pathParts);
        $path = Mage::helper('M2ePro')->escapeHtml($path);

        $fullPath = $path;
        strlen($path) > 135 && $path = substr($path, 0, 135) . '...';

        $foundInWord = Mage::helper('M2ePro')->__('Found In: ');

        $xpath  = Mage::helper('M2ePro')->escapeHtml($row->getData('xpath'));
        $xmlTag = Mage::helper('M2ePro')->escapeHtml($row->getData('xml_tag'));

        return <<<HTML
<div style="margin-left: 3px">
    <a href="javascript:void(0);"
       class="specific_search_result_row" xpath="{$xpath}" xml_tag="{$xmlTag}" xml_title="{$title}">{$title}</a><br/>

    <span style="font-weight: bold;">{$foundInWord}</span>
    <span title="{$fullPath}">{$path}</span><br/>
</div>
HTML;
    }

    public function callbackColumnIsDesired($value, $row, $column, $isExport)
    {
        return $value ? Mage::helper('M2ePro')->__('Yes') : Mage::helper('M2ePro')->__('No');
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $xpath    = Mage::helper('M2ePro')->escapeHtml($row->getData('xpath'));
        $xmlTag   = Mage::helper('M2ePro')->escapeHtml($row->getData('xml_tag'));
        $xmlTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('title'));

        $select = Mage::helper('M2ePro')->__('Select');
        return <<<HTML
<a href="javascript:void(0);" class="specific_search_result_row" xpath="{$xpath}"
                                                                 xml_tag="{$xmlTag}"
                                                                 xml_title="{$xmlTitle}">
{$select}
</a>
HTML;
    }

    //########################################

    public function setMarketplaceId($marketplaceId)
    {
        $this->marketplaceId = $marketplaceId;
        return $this;
    }

    public function setProductDataNick($productDataNick)
    {
        $this->productDataNick = $productDataNick;
        return $this;
    }

    public function setCurrentXpath($indexedXpath)
    {
        $this->currentXpath = preg_replace('/-\d+/', '', $indexedXpath);
        return $this;
    }

    public function setAllRenderedSpecifics(array $specifics)
    {
        $this->allRenderedSpecifics = $this->replaceWithDictionaryXpathes($specifics);
        return $this;
    }

    public function setBlockRenderedSpecifics(array $specifics)
    {
        $this->blockRenderedSpecifics = $this->replaceWithDictionaryXpathes($specifics);
        return $this;
    }

    public function setSelectedSpecifics(array $specifics)
    {
        $this->selectedSpecifics = $this->replaceWithDictionaryXpathes($specifics);
        return $this;
    }

    public function setOnlyDesired($value)
    {
        $this->onlyDesired = $value;
        return $this;
    }

    public function setSearchQuery($searchQuery)
    {
        $this-> searchQuery = $searchQuery;
        return $this;
    }

    // ---------------------------------------

    protected function replaceWithDictionaryXpathes(array $xPathes)
    {
        return array_map(
            function($el) {
            return preg_replace('/-\d+/', '', $el); 
            }, $xPathes
        );
    }

    //########################################

    public function getGridUrl()
    {
        return false;
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}