<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Product_Instruction_Processor
{
    protected $_component = null;

    protected $_maxListingsProductsCount = null;

    /** @var Ess_M2ePro_Model_Listing_Product_Instruction_Handler_Interface[] */
    protected $_handlers = array();

    //########################################

    public function setComponent($component)
    {
        $this->_component = $component;
        return $this;
    }

    public function setMaxListingsProductsCount($count)
    {
        $this->_maxListingsProductsCount = $count;
        return $this;
    }

    //########################################

    public function registerHandler(Ess_M2ePro_Model_Listing_Product_Instruction_Handler_Interface $handler)
    {
        $this->_handlers[] = $handler;
        return $this;
    }

    //########################################

    public function process()
    {
        $this->deleteInstructionsWithoutListingProducts();

        $listingsProducts = $this->getNeededListingsProducts();

        $instructions = $this->loadInstructions($listingsProducts);
        if (empty($instructions)) {
            return;
        }

        foreach ($instructions as $listingProductId => $listingProductInstructions) {
            try {
                $handlerInput = Mage::getModel('M2ePro/Listing_Product_Instruction_Handler_Input');
                $handlerInput->setListingProduct($listingsProducts[$listingProductId]);
                $handlerInput->setInstructions($listingProductInstructions);

                foreach ($this->_handlers as $handler) {
                    $handler->process($handlerInput);

                    if ($handlerInput->getListingProduct()->isDeleted()) {
                        break;
                    }
                }
            } catch (\Exception $exception) {
                Mage::helper('M2ePro/Module_Exception')->process($exception);
            }

            Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->remove(
                array_keys($listingProductInstructions)
            );
        }
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product[] $listingsProducts
     * @return Ess_M2ePro_Model_Listing_Product_Instruction[][]
     */
    protected function loadInstructions(array $listingsProducts)
    {
        if (empty($listingsProducts)) {
            return array();
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Instruction_Collection $collection */
        $instructionCollection = Mage::getResourceModel('M2ePro/Listing_Product_Instruction_Collection');
        $instructionCollection->applySkipUntilFilter();
        $instructionCollection->addFieldToFilter('listing_product_id', array_keys($listingsProducts));

        /** @var Ess_M2ePro_Model_Listing_Product_Instruction[] $instructions */
        $instructions = $instructionCollection->getItems();

        $instructionsByListingsProducts = array();

        foreach ($instructions as $instruction) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = $listingsProducts[$instruction->getListingProductId()];
            $instruction->setListingProduct($listingProduct);

            $instructionsByListingsProducts[$instruction->getListingProductId()][$instruction->getId()] = $instruction;
        }

        return $instructionsByListingsProducts;
    }

    /**
     * @return array
     */
    protected function getNeededListingsProducts()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Instruction_Collection $collection */
        $collection = Mage::getModel('M2ePro/Listing_Product_Instruction')->getCollection();
        $collection->applyNonBlockedFilter();
        $collection->applySkipUntilFilter();
        $collection->addFieldToFilter('main_table.component', $this->_component);

        $collection->setOrder('MAX(main_table.priority)', 'DESC');
        $collection->setOrder('MIN(main_table.create_date)', 'ASC');

        $collection->getSelect()->limit($this->_maxListingsProductsCount);
        $collection->getSelect()->group('main_table.listing_product_id');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('main_table.listing_product_id');

        $ids = $collection->getColumnValues('listing_product_id');
        if (empty($ids)) {
            return array();
        }

        $listingsProductsCollection = Mage::helper('M2ePro/Component_'.$this->_component)
            ->getCollection('Listing_Product');
        $listingsProductsCollection->addFieldToFilter('id', $ids);

        return $listingsProductsCollection->getItems();
    }

    //########################################

    protected function deleteInstructionsWithoutListingProducts()
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Instruction_Collection $collection */
        $collection = Mage::getModel('M2ePro/Listing_Product_Instruction')->getCollection();
        $collection->getSelect()->joinLeft(
            array('second_table' => Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable()),
            'main_table.listing_product_id = second_table.id'
        );
        $collection->getSelect()->where('second_table.id IS NULL');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns('main_table.id');

        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->remove(
            $collection->getColumnValues('id')
        );
    }

    //########################################
}
