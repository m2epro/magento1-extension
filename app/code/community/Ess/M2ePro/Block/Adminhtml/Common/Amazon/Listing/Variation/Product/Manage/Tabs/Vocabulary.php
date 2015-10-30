<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Variation_Product_Manage_Tabs_Vocabulary
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $listingProductId;

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    protected $listingProduct;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/common/amazon/listing/variation/product/manage/tabs/vocabulary.phtml');

        return $this;
    }

    //########################################

    /**
     * @param mixed $listingProductId
     * @return $this
     */
    public function setListingProductId($listingProductId)
    {
        $this->listingProductId = $listingProductId;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->listingProductId;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product|null
     */
    public function getListingProduct()
    {
        if (is_null($this->listingProduct)) {
            $this->listingProduct = Mage::helper('M2ePro/Component_Amazon')
                ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->listingProduct;
    }

    //########################################

    public function prepareData()
    {
        $localVocabulary = array();
        $fixedAttributes = array();
        $matchedAttributes = $this->getListingProduct()->getChildObject()
            ->getVariationManager()->getTypeModel()->getMatchedAttributes();
        $magentoProductVariations = $this->getListingProduct()
            ->getMagentoProduct()
            ->getVariationInstance()
            ->getVariationsTypeStandard();

        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');
        $vocabularyData = $vocabularyHelper->getLocalData();

        if (empty($matchedAttributes)) {
            return array(
                'local_vocabulary' => $localVocabulary,
                'fixed_attributes' => $fixedAttributes
            );
        }

        foreach ($matchedAttributes as $magentoAttr => $channelAttr) {
            foreach ($vocabularyData as $attribute => $attributeData) {

                if (in_array($magentoAttr, $attributeData['names']) || $attribute == $channelAttr) {

                    if (!in_array($magentoAttr, $attributeData['names'])) {
                        $fixedAttributes[$magentoAttr][] = $attribute;
                    }

                    $localVocabulary[$magentoAttr][$attribute] = array();

                    if (!empty($attributeData['options'])) {
                        foreach ($magentoProductVariations['set'][$magentoAttr] as $magentoOption) {

                            foreach ($attributeData['options'] as $attributeOptions) {

                                if (in_array($magentoOption, $attributeOptions)) {
                                    $localVocabulary[$magentoAttr][$attribute][$magentoOption][] = $attributeOptions;
                                }
                            }
                        }
                    }

                    if (!empty($fixedAttributes[$magentoAttr]) &&
                        in_array($attribute, $fixedAttributes[$magentoAttr]) &&
                        empty($localVocabulary[$magentoAttr][$attribute])) {

                        unset($localVocabulary[$magentoAttr][$attribute]);
                        if (empty($localVocabulary[$magentoAttr])) {
                            unset($localVocabulary[$magentoAttr]);
                        }
                    }
                }
            }
        }

        return array(
            'local_vocabulary' => $localVocabulary,
            'fixed_attributes' => $fixedAttributes
        );
    }

    //########################################
}
