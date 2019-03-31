<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_List_Validator
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Validator
{
    protected $isVerifyCall = false;

    //########################################

    public function validate()
    {
        if (!$this->getListingProduct()->isListable()) {

            // M2ePro_TRANSLATIONS
            // Item is Listed or not available
            $this->addMessage('Item is Listed or not available');

            return false;
        }

        if ($this->getListingProduct()->isHidden()) {

            // M2ePro_TRANSLATIONS
            // The List action cannot be executed for this Item as it has a Listed (Hidden) status. You have to stop Item manually first to run the List action for it.
            $this->addMessage(
                'The List action cannot be executed for this Item as it has a Listed (Hidden) status.
                You have to stop Item manually first to run the List action for it.'
            );

            return false;
        }

        if (!$this->validateSameProductAlreadyListed()) {
            return false;
        }

        if (!$this->validateIsVariationProductWithoutVariations()) {
            return false;
        }

        if ($this->getEbayListingProduct()->isVariationsReady()) {

            if (!$this->validateVariationsOptions()) {
                return false;
            }
        }

        if (!$this->validateCategory()) {
            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        if (!$this->validateQty()) {
            return false;
        }

        return true;
    }

    //########################################

    protected function validateSameProductAlreadyListed()
    {
        if ($this->isVerifyCall) {
            return true;
        }

        $params = $this->getParams();
        if ($params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            return true;
        }

        $config = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/ebay/connector/listing/', 'check_the_same_product_already_listed'
        );

        if (empty($config)) {
            return true;
        }

        $listingTable = Mage::getResourceModel('M2ePro/Listing')->getMainTable();
        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');

        $listingProductCollection
            ->getSelect()
            ->join(array('l'=>$listingTable),'`main_table`.`listing_id` = `l`.`id`',array());

        $listingProductCollection
            ->addFieldToFilter('status', array('neq' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED))
            ->addFieldToFilter('product_id',$this->getListingProduct()->getProductId())
            ->addFieldToFilter('account_id',$this->getAccount()->getId())
            ->addFieldToFilter('marketplace_id',$this->getMarketplace()->getId());

        if (!empty($params['skip_check_the_same_product_already_listed_ids'])) {

            $listingProductCollection->addFieldToFilter(
                'listing_product_id', array('nin' => $params['skip_check_the_same_product_already_listed_ids'])
            );
        }

        /** @var Ess_M2ePro_Model_Listing_Product $theSameListingProduct */
        $theSameListingProduct = $listingProductCollection->getFirstItem();

        if (!$theSameListingProduct->getId()) {
            return true;
        }

        $this->addMessage(Mage::helper('M2ePro/Module_Log')->encodeDescription(
            'There is another Item with the same eBay User ID, '.
            'Product ID and eBay Site presented in "%listing_title%" (%listing_id%) Listing.',
            array(
                '!listing_title' => $theSameListingProduct->getListing()->getTitle(),
                '!listing_id' => $theSameListingProduct->getListing()->getId()
            )
        ));

        return false;
    }

    //########################################

    public function setIsVerifyCall($value)
    {
        $this->isVerifyCall = $value;
        return $this;
    }

    //########################################
}