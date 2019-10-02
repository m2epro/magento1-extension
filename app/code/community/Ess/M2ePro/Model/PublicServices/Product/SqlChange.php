<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/*
    $model = Mage::getModel('M2ePro/PublicServices_Product_SqlChange');

    // notify M2E Pro about some change of product with ID 17
    $model->markProductChanged(17);

    // make price change of product with ID 18 and then notify M2E Pro
    $model->markPriceChanged(18);

    // make QTY change of product with ID 19 and then notify M2E Pro
    $model->markQtyChanged(19);

    // make status change of product with ID 20 and then notify M2E Pro
    $model->markStatusChanged(20);

    $model->applyChanges();
*/

class Ess_M2ePro_Model_PublicServices_Product_SqlChange
{
    const VERSION = '2.0.1';

    const INSTRUCTION_TYPE_PRODUCT_CHANGED = 'sql_change_product_changed';
    const INSTRUCTION_TYPE_STATUS_CHANGED  = 'sql_change_status_changed';
    const INSTRUCTION_TYPE_QTY_CHANGED     = 'sql_change_qty_changed';
    const INSTRUCTION_TYPE_PRICE_CHANGED   = 'sql_change_price_changed';

    const INSTRUCTION_INITIATOR = 'public_services_sql_change_processor';

    protected $_preventDuplicatesMode = true;

    protected $_changesData = array();

    //########################################

    public function enablePreventDuplicatesMode()
    {
        $this->_preventDuplicatesMode = true;
    }

    public function disablePreventDuplicatesMode()
    {
        $this->_preventDuplicatesMode = false;
    }

    //########################################

    public function applyChanges()
    {
        $instructionsData = $this->getInstructionsData();

        if ($this->_preventDuplicatesMode) {
            $instructionsData = $this->filterExistedInstructions($instructionsData);
        }

        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add($instructionsData);

        $this->flushChanges();

        return $this;
    }

    /**
     * @return $this
     */
    public function flushChanges()
    {
        $this->_changesData = array();
        return $this;
    }

    //########################################

    /**
     * Backward compatibility issue
     * @param $productId
     * @return $this
     */
    public function markQtyWasChanged($productId)
    {
        return $this->markProductChanged($productId);
    }

    /**
     * Backward compatibility issue
     * @param $productId
     * @return $this
     */
    public function markPriceWasChanged($productId)
    {
        return $this->markProductChanged($productId);
    }

    /**
     * Backward compatibility issue
     * @param $productId
     * @return $this
     */
    public function markStatusWasChanged($productId)
    {
        return $this->markProductChanged($productId);
    }

    //----------------------------------------

    public function markProductAttributeChanged(
        $productId,
        $attributeCode,
        $storeId,
        $valueOld = null,
        $valueNew = null
    ) {
        throw new Ess_M2ePro_Model_Exception_Logic('Method is not supported.');
    }

    //########################################

    public function markProductChanged($productId)
    {
        $this->_changesData[] = array(
            'product_id'       => (int)$productId,
            'instruction_type' => self::INSTRUCTION_TYPE_PRODUCT_CHANGED,
        );
        return $this;
    }

    public function markStatusChanged($productId)
    {
        $this->_changesData[] = array(
            'product_id'       => (int)$productId,
            'instruction_type' => self::INSTRUCTION_TYPE_STATUS_CHANGED,
        );
        return $this;
    }

    public function markQtyChanged($productId)
    {
        $this->_changesData[] = array(
            'product_id'       => (int)$productId,
            'instruction_type' => self::INSTRUCTION_TYPE_QTY_CHANGED,
        );
        return $this;
    }

    public function markPriceChanged($productId)
    {
        $this->_changesData[] = array(
            'product_id'       => (int)$productId,
            'instruction_type' => self::INSTRUCTION_TYPE_PRICE_CHANGED,
        );
        return $this;
    }

    //########################################

    protected function getInstructionsData()
    {
        if (empty($this->_changesData)) {
            return array();
        }

        $productInstructionTypes = array();

        foreach ($this->_changesData as $changeData) {
            $productId = (int)$changeData['product_id'];

            $productInstructionTypes[$productId][] = $changeData['instruction_type'];
            $productInstructionTypes[$productId] = array_unique($productInstructionTypes[$productId]);
        }

        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $connRead = $resource->getConnection('core_read');

        $listingProductTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_listing_product');
        $variationTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_listing_product_variation');
        $variationOptionTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_listing_product_variation_option');

        $instructionsData = array();

        foreach (array_chunk($productInstructionTypes, 1000, true) as $productInstructionTypesPart) {
            $simpleProductsSelect = $connRead
                ->select()
                ->from($listingProductTable, array('magento_product_id' => 'product_id', 'listing_product_id' => 'id'))
                ->where('product_id IN (?)', array_keys($productInstructionTypesPart));

            $variationsProductsSelect = $connRead
                ->select()
                ->from(array('lpvo' => $variationOptionTable), array('magento_product_id' => 'product_id'))
                ->joinLeft(
                    array('lpv' => $variationTable),
                    'lpv.id = lpvo.listing_product_variation_id',
                    array('listing_product_id')
                )
                ->where('product_id IN (?)', array_keys($productInstructionTypesPart));

            $stmtQuery = $connRead
                ->select()
                ->union(array($simpleProductsSelect, $variationsProductsSelect))
                ->query();

            while ($row = $stmtQuery->fetch()) {
                $magentoProductId = (int)$row['magento_product_id'];
                $listingProductId = (int)$row['listing_product_id'];

                foreach ($productInstructionTypesPart[$magentoProductId] as $instructionType) {
                    $instructionsData[] = array(
                        'listing_product_id' => $listingProductId,
                        'type'               => $instructionType,
                        'initiator'          => self::INSTRUCTION_INITIATOR,
                        'priority'           => 50,
                    );
                }
            }
        }

        return $instructionsData;
    }

    protected function filterExistedInstructions(array $instructionsData)
    {
        $indexedInstructionsData = array();

        foreach ($instructionsData as $instructionData) {
            $key = $instructionData['listing_product_id'].'##'.$instructionData['type'];
            $indexedInstructionsData[$key] = $instructionData;
        }

        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        $connRead = $resource->getConnection('core_read');

        $instructionTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_listing_product_instruction');

        $stmt = $connRead->select()
            ->from($instructionTable, array('listing_product_id', 'type'))
            ->query();

        while ($row = $stmt->fetch()) {
            $listingProductId = (int)$row['listing_product_id'];
            $type             = $row['type'];

            if (isset($indexedInstructionsData[$listingProductId.'##'.$type])) {
                unset($indexedInstructionsData[$listingProductId.'##'.$type]);
            }
        }

        return array_values($indexedInstructionsData);
    }

    //########################################
}
