<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'listing/product/inspect_direct_changes';

    const KEY_PREFIX = '/listing/product/inspector';

    const INSTRUCTION_TYPE      = 'inspector_triggered';
    const INSTRUCTION_INITIATOR = 'direct_changes_inspector';
    const INSTRUCTION_PRIORITY  = 10;

    //########################################

    protected function performActions()
    {
        foreach (Mage::helper('M2ePro/Component')->getEnabledComponents() as $component) {
            if (!$this->isEnabled()) {
                continue;
            }

            $allowedListingsProductsCount = $this->calculateAllowedListingsProductsCount($component);
            if ($allowedListingsProductsCount <= 0) {
                continue;
            }

            $listingsProductsIds = $this->getNextListingsProductsIds($component, $allowedListingsProductsCount);
            if (empty($listingsProductsIds)) {
                $this->setLastListingProductId($component, 0);
                continue;
            }

            $instructionsData = array();

            foreach ($listingsProductsIds as $listingProductId) {
                $instructionsData[] = array(
                    'listing_product_id' => $listingProductId,
                    'type'               => self::INSTRUCTION_TYPE,
                    'initiator'          => self::INSTRUCTION_INITIATOR,
                    'priority'           => self::INSTRUCTION_PRIORITY,
                );
            }

            Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add($instructionsData);

            $this->setLastListingProductId($component, end($listingsProductsIds));
        }
    }

    //########################################

    protected function calculateAllowedListingsProductsCount($component)
    {
        $maxAllowedInstructionsCount = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::KEY_PREFIX.'/'.$component.'/', 'max_allowed_instructions_count'
        );

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Instruction_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_Instruction_Collection');
        $currentInstructionsCount = $collection->applySkipUntilFilter()
            ->addFieldToFilter('component', $component)
            ->addFieldToFilter('initiator', self::INSTRUCTION_INITIATOR)
            ->getSize();

        if ($currentInstructionsCount > $maxAllowedInstructionsCount) {
            return 0;
        }

        return $maxAllowedInstructionsCount - $currentInstructionsCount;
    }

    protected function getNextListingsProductsIds($component, $limit)
    {
        $collection = Mage::getResourceModel('M2ePro/Listing_Product_Collection');
        $collection->addFieldToFilter('component_mode', $component);
        $collection->addFieldToFilter('id', array('gt' => $this->getLastListingProductId($component)));
        $collection->getSelect()->order(array('id ASC'));
        $collection->getSelect()->limit($limit);

        return $collection->getColumnValues('id');
    }

    //########################################

    protected function isEnabled()
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(self::KEY_PREFIX.'/', 'mode');
    }

    // ---------------------------------------

    protected function getLastListingProductId($component)
    {
        $configValue = $this->getRegistryValue(self::KEY_PREFIX.'/'.$component.'/last_listing_product_id/');

        if ($configValue === null) {
            return 0;
        }

        return $configValue;
    }

    protected function setLastListingProductId($component, $listingProductId)
    {
        $this->setRegistryValue(self::KEY_PREFIX.'/'.$component.'/last_listing_product_id/', (int)$listingProductId);
    }

    //########################################
}
