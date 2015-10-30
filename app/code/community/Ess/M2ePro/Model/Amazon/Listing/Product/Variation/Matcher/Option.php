<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Option
{
    /** @var Ess_M2ePro_Model_Magento_Product $magentoProduct */
    private $magentoProduct = null;

    private $destinationOptions = array();

    private $destinationOptionsLocalVocabularyNames = array();

    private $destinationOptionsServerVocabularyNames = array();

    private $matchedAttributes = array();

    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Option_Resolver $resolver */
    private $resolver = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return $this
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    // ---------------------------------------

//    $destinationOptions = array(
//        'B00005N5PF' => array(
//          'Color' => 'Red',
//          'Size'  => 'XL',
//        ),
//        ...
//    )

    /**
     * @param array $destinationOptions
     * @return $this
     */
    public function setDestinationOptions(array $destinationOptions)
    {
        $this->destinationOptions = $destinationOptions;

        $this->destinationOptionsLocalVocabularyNames  = array();
        $this->destinationOptionsServerVocabularyNames = array();

        return $this;
    }

    /**
     * @param array $matchedAttributes
     * @return $this
     */
    public function setMatchedAttributes(array $matchedAttributes)
    {
        $this->matchedAttributes = $matchedAttributes;
        return $this;
    }

    //########################################

//    $sourceOption = array(
//         'Color' => 'red',
//         'Size'  => 'L'
//    )

    /**
     * @param array $sourceOption
     * @return null|int
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getMatchedOptionGeneralId(array $sourceOption)
    {
        $this->validate();

        if ($generalId = $this->matchGeneralIdByNames($sourceOption)) {
            return $generalId;
        }

        if ($generalId = $this->matchGeneralIdByLocalVocabulary($sourceOption)) {
            return $generalId;
        }

        if ($generalId = $this->matchGeneralIdByServerVocabulary($sourceOption)) {
            return $generalId;
        }

        return null;
    }

    //########################################

    private function validate()
    {
        if (is_null($this->magentoProduct)) {
            throw new Ess_M2ePro_Model_Exception('Magento Product was not set.');
        }

        if (empty($this->destinationOptions)) {
            throw new Ess_M2ePro_Model_Exception('Destination Options is empty.');
        }
    }

    // ---------------------------------------

    private function matchGeneralIdByNames(array $sourceOption)
    {
        $sourceOptionNames = array();
        foreach ($sourceOption as $attribute => $option) {
            $sourceOptionNames[$attribute] = $this->prepareOptionNames($option);
        }

        $this->getResolver()
            ->setSourceOption($sourceOptionNames)
            ->setDestinationOptions($this->destinationOptions)
            ->setMatchedAttributes($this->matchedAttributes);

        return $this->getResolver()->resolve()->getResolvedGeneralId();
    }

    private function matchGeneralIdByLocalVocabulary(array $sourceOption)
    {
        $this->getResolver()
            ->setSourceOption($this->getSourceOptionNames($sourceOption))
            ->setDestinationOptions($this->getDestinationOptionLocalVocabularyNames())
            ->setMatchedAttributes($this->matchedAttributes);

        return $this->getResolver()->resolve()->getResolvedGeneralId();
    }

    private function matchGeneralIdByServerVocabulary(array $sourceOption)
    {
        $this->getResolver()
            ->setSourceOption($this->getSourceOptionNames($sourceOption))
            ->setDestinationOptions($this->getDestinationOptionServerVocabularyNames())
            ->setMatchedAttributes($this->matchedAttributes);

        return $this->getResolver()->resolve()->getResolvedGeneralId();
    }

    // ---------------------------------------

    private function getSourceOptionNames($sourceOption)
    {
        $magentoOptionNames = $this->magentoProduct->getVariationInstance()->getTitlesVariationSet();

        $resultNames = array();
        foreach ($sourceOption as $attribute => $option) {
            $names = array();
            if (isset($magentoOptionNames[$attribute])) {
                $names = $magentoOptionNames[$attribute]['values'][$option];
            }

            $resultNames[$attribute] = $this->prepareOptionNames($option, $names);
        }

        return $resultNames;
    }

    private function getDestinationOptionLocalVocabularyNames()
    {
        if (!empty($this->destinationOptionsLocalVocabularyNames)) {
            return $this->destinationOptionsLocalVocabularyNames;
        }

        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

        foreach ($this->destinationOptions as $generalId => $destinationOption) {
            foreach ($destinationOption as $attributeName => $optionName) {
                $this->destinationOptionsLocalVocabularyNames[$generalId][$attributeName] = $this->prepareOptionNames(
                    $optionName, $vocabularyHelper->getLocalOptionNames($attributeName, $optionName)
                );
            }
        }

        return $this->destinationOptionsLocalVocabularyNames;
    }

    private function getDestinationOptionServerVocabularyNames()
    {
        if (!empty($this->destinationOptionsServerVocabularyNames)) {
            return $this->destinationOptionsServerVocabularyNames;
        }

        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');

        foreach ($this->destinationOptions as $generalId => $destinationOption) {
            foreach ($destinationOption as $attributeName => $optionName) {
                $this->destinationOptionsServerVocabularyNames[$generalId][$attributeName] = $this->prepareOptionNames(
                    $optionName, $vocabularyHelper->getServerOptionNames($attributeName, $optionName)
                );
            }
        }

        return $this->destinationOptionsServerVocabularyNames;
    }

    //########################################

    private function getResolver()
    {
        if (!is_null($this->resolver)) {
            return $this->resolver;
        }

        $this->resolver = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Option_Resolver');
        return $this->resolver;
    }

    private function prepareOptionNames($option, array $names = array())
    {
        $names[] = $option;
        $names = array_unique($names);

        $names = array_map('trim', $names);
        $names = array_map('strtolower', $names);

        return $names;
    }

    //########################################
}