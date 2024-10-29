<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract
{
    const INSTRUCTION_INITIATOR = 'magento_product_change_processor';
    const INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED = 'magento_product_data_potentially_changed';

    /** @deprecated */
    const INSTRUCTION_TYPE_PRODUCT_QTY_DATA_POTENTIALLY_CHANGED = 'magento_product_qty_data_potentially_changed';
    /** @deprecated */
    const INSTRUCTION_TYPE_PRODUCT_PRICE_DATA_POTENTIALLY_CHANGED = 'magento_product_price_data_potentially_changed';
    /** @deprecated */
    const INSTRUCTION_TYPE_PRODUCT_STATUS_DATA_POTENTIALLY_CHANGED = 'magento_product_status_data_potentially_changed';

    const INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED = 'magmi_plugin_product_changed';

    /** @var Ess_M2ePro_Model_Listing_Product */
    protected $_listingProduct = null;

    protected $_defaultInstructionTypes = array();

    //########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->_listingProduct = $listingProduct;
        return $this;
    }

    public function setDefaultInstructionTypes(array $instructionTypes)
    {
        $this->_defaultInstructionTypes = $instructionTypes;
        return $this;
    }

    //########################################

    abstract public function getTrackingAttributes();

    //########################################

    public function process($changedAttributes = array())
    {
        $listingProductInstructionsData = array();

        foreach ($this->_defaultInstructionTypes as $instructionType) {
            $listingProductInstructionsData[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => $instructionType,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 100,
            );
        }

        foreach ($this->getInstructionsDataByAttributes($changedAttributes) as $instructionData) {
            $listingProductInstructionsData[] = array(
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type'               => $instructionData['type'],
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => $instructionData['priority'],
            );
        }

        Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add($listingProductInstructionsData);
    }

    //########################################

    abstract protected function getInstructionsDataByAttributes(array $attributes);

    //########################################

    protected function getListingProduct()
    {
        return $this->_listingProduct;
    }

    //########################################
}
