<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Listing_Product_ProcessReviseTotal extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'listing/product/process_revise_total';

    const KEY_PREFIX = '/listing/product/revise/total';

    const INSTRUCTION_TYPE      = 'revise_total_triggered';
    const INSTRUCTION_INITIATOR = 'revise_total_processor';
    const INSTRUCTION_PRIORITY  = 20;

    //########################################

    protected function performActions()
    {
        $components = Mage::helper('M2ePro/Component')->getEnabledComponents();

        foreach ($components as $component) {
            if (!$this->isEnabled($component) || $this->isFinished($component)) {
                continue;
            }

            $allowedListingsProductsCount = $this->calculateAllowedListingsProductsCount($component);
            if ($allowedListingsProductsCount <= 0) {
                continue;
            }

            $listingsProductsIds = $this->getNextListingsProductsIds($component, $allowedListingsProductsCount);
            if (empty($listingsProductsIds)) {
                $this->finish($component);
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
        $collection->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED);
        $collection->getSelect()->order(array('id ASC'));
        $collection->getSelect()->limit($limit);

        return $collection->getColumnValues('id');
    }

    //########################################

    protected function isEnabled($component)
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            self::KEY_PREFIX.'/'.$component.'/', 'mode'
        );
    }

    protected function isFinished($component)
    {
        return (bool)$this->getRegistryValue(self::KEY_PREFIX.'/'.$component.'/end_date/');
    }

    // ---------------------------------------

    protected function finish($component)
    {
        $this->setRegistryValue(
            self::KEY_PREFIX.'/'.$component.'/end_date/', Mage::helper('M2ePro')->getCurrentGmtDate()
        );
        $this->setLastListingProductId($component, 0);
    }

    // ---------------------------------------

    protected function getLastListingProductId($component)
    {
        return (int)$this->getRegistryValue(self::KEY_PREFIX.'/'.$component.'/last_listing_product_id/');
    }

    protected function setLastListingProductId($component, $listingProductId)
    {
        $this->setRegistryValue(self::KEY_PREFIX.'/'.$component.'/last_listing_product_id/', $listingProductId);
    }

    //########################################
}
