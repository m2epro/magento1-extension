<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */
class Ess_M2ePro_Model_Resource_Listing_Product_Instruction extends Ess_M2ePro_Model_Resource_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Listing_Product_Instruction', 'id');
    }

    //########################################

    public function add(array $instructionsData)
    {
        if (empty($instructionsData)) {
            return;
        }

        $listingsProductsIds = array();

        foreach ($instructionsData as $instructionData) {
            $listingsProductsIds[] = $instructionData['listing_product_id'];
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingsProductsCollection */
        $listingsProductsCollection = Mage::getResourceModel('M2ePro/Listing_Product_Collection');
        $instructionSelectExpression = new Zend_Db_Expr(
            "IFNULL(CONCAT('[\"', GROUP_CONCAT(DISTINCT lpi.type SEPARATOR '\",\"'), '\"]'), '[]')"
        );

        $listingsProductsCollection
            ->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'id' => 'main_table.id',
                'component_mode' => 'main_table.component_mode',
            ))
            ->joinLeft(
                array(
                    'lpi' => Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_listing_product_instruction'),
                ),
                'lpi.listing_product_id = main_table.id AND lpi.component = main_table.component_mode',
                array('instruction_json_types' => $instructionSelectExpression)
            )
            ->where('main_table.id IN (?)', array_unique($listingsProductsIds))
            ->group(array('main_table.id', 'main_table.component_mode'))
            ->order('main_table.id');

        foreach ($instructionsData as $index => &$instructionData) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct = $listingsProductsCollection->getItemById($instructionData['listing_product_id']);
            if ($listingProduct === null) {
                unset($instructionsData[$index]);
                continue;
            }

            $encodedInstructionTypes = $listingProduct->getData('instruction_json_types');
            $instructionTypes = Mage::helper('M2ePro')->jsonDecode($encodedInstructionTypes);

            if (in_array($instructionData['type'], $instructionTypes, true)) {
                unset($instructionsData[$index]);
                continue;
            }

            $instructionData['component']   = $listingProduct->getComponentMode();
            $instructionData['create_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        }

        if (empty($instructionsData)) {
            return;
        }

        $this->_getWriteAdapter()->insertMultiple($this->getMainTable(), $instructionsData);
    }

    public function remove(array $instructionsIds)
    {
        if (empty($instructionsIds)) {
            return;
        }

        $this->_getWriteAdapter()->delete(
            $this->getMainTable(),
            array(
                'id IN (?)' => $instructionsIds,
                'skip_until IS NULL OR ? > skip_until' => Mage::helper('M2ePro')->getCurrentGmtDate()
            )
        );
    }

    //########################################
}
