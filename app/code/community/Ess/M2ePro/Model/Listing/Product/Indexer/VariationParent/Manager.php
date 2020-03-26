<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Resource_Ebay_Listing_Product_Indexer_VariationParent as EbayIndexer;
use Ess_M2ePro_Model_Resource_Amazon_Listing_Product_Indexer_VariationParent as AmazonIndexer;
use Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Indexer_VariationParent as WalmartIndexer;

class Ess_M2ePro_Model_Listing_Product_Indexer_VariationParent_Manager extends Ess_M2ePro_Model_Abstract
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    const INDEXER_LIFETIME = 1800;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $listing = null;
        $args = func_get_args();
        !empty($args[0][0]) && $listing = $args[0][0];

        $this->_listing = $listing;
    }

    //########################################

    public function prepare()
    {
        if ($this->isUpToDate()) {
            return;
        }

        /** @var EbayIndexer|AmazonIndexer|WalmartIndexer $resourceModel */
        $resourceModel = Mage::getResourceModel(
            'M2ePro/'.ucfirst($this->_listing->getComponentMode()) . '_Listing_Product_Indexer_VariationParent'
        );
        $resourceModel->clear($this->_listing->getId());
        $resourceModel->build($this->_listing);

        $this->markAsIsUpToDate();
    }

    public function markInvalidated()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeValue(
            $this->getUpToDateCacheKey()
        );
        return $this;
    }

    //########################################

    protected function isUpToDate()
    {
        return Mage::helper('M2ePro/Data_Cache_Permanent')->getValue(
            $this->getUpToDateCacheKey()
        );
    }

    protected function markAsIsUpToDate()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->setValue(
            $this->getUpToDateCacheKey(),
            'true',
            array('listing_product_indexer_variation_parent'),
            self::INDEXER_LIFETIME
        );
        return $this;
    }

    protected function getUpToDateCacheKey()
    {
        return '_listing_product_indexer_variation_parent_up_to_date_for_listing_id_' . $this->_listing->getId();
    }

    //########################################
}
