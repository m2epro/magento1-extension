<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_Description as Description;

class Ess_M2ePro_Observer_Magento_Cms_Block_SaveAfter extends Ess_M2ePro_Observer_Abstract
{
    const INSTRUCTION_INITIATOR = 'magento_static_block_observer';

    //########################################

    public function process()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Magento_StaticBlock $block */
        $block = $this->getEvent()->getData('object');
        if ($block->getOrigData('content') == $block->getData('content')) {
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Template_Description_Collection $templates */
        $templates = Mage::getModel('M2ePro/Ebay_Template_Description')->getCollection()->addFieldToFilter(
            'description_template', array('like' => "%{$block->getIdentifier()}%")
        );

        foreach ($templates as $template) {
            /** @var Ess_M2ePro_Model_Template_Description $template */

            /** @var Ess_M2ePro_Model_Ebay_Template_Description_AffectedListingsProducts $affectedListingsProducts */
            $affectedListingsProducts = Mage::getModel('M2ePro/Ebay_Template_Description_AffectedListingsProducts');
            $affectedListingsProducts->setModel($template);

            $listingsProductsInstructionsData = array();

            foreach ($affectedListingsProducts->getIds() as $listingProductId) {
                $listingsProductsInstructionsData[] = array(
                    'listing_product_id' => $listingProductId,
                    'type'               => Description::INSTRUCTION_TYPE_MAGENTO_STATIC_BLOCK_IN_DESCRIPTION_CHANGED,
                    'initiator'          => self::INSTRUCTION_INITIATOR,
                    'priority'           => 30
                );
            }

            Mage::getResourceModel('M2ePro/Listing_Product_Instruction')->add($listingsProductsInstructionsData);
        }
    }

    //########################################
}
