<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Template_AffectedListingsProducts_AffectedListingsProductsAbstract
    extends Ess_M2ePro_Model_Template_AffectedListingsProductsAbstract
{
    //########################################

    abstract public function getTemplateNick();

    //########################################

    public function loadCollection(array $filters = array())
    {
        $ids = array();

        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');
        $templateManager->setTemplate($this->getTemplateNick());

        $tempListingsProducts = $templateManager->getAffectedOwnerObjects(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING_PRODUCT,
            $this->_model->getId(),
            true,
            array('id')
        );

        foreach ($tempListingsProducts as $tempListingsProduct) {
            if (!isset($listingsProductsIds[$tempListingsProduct['id']])) {
                $ids[$tempListingsProduct['id']] = $tempListingsProduct['id'];
            }
        }

        $listings = $templateManager->getAffectedOwnerObjects(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING,
            $this->_model->getId(),
            false
        );

        foreach ($listings as $listing) {
            $listingAffectedProducts = Mage::getModel('M2ePro/Ebay_Listing_AffectedListingsProducts');
            $listingAffectedProducts->setModel($listing);

            $tempListingsProducts = $listingAffectedProducts->getData(
                array('id'),
                array('template' => $this->getTemplateNick())
            );

            foreach ($tempListingsProducts as $tempListingsProduct) {
                if (!isset($listingsProductsIds[$tempListingsProduct['id']])) {
                    $ids[$tempListingsProduct['id']] = $tempListingsProduct['id'];
                }
            }
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('id', array('in' => $ids));

        return $listingProductCollection;
    }

    //########################################
}
