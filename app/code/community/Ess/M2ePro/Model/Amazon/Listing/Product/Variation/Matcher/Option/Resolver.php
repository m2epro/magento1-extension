<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Option_Resolver
{
    protected $_sourceOption = array();

    protected $_destinationOptions = array();

    protected $_matchedAttributes = array();

    protected $_resolvedGeneralId = null;

    //########################################

    /**
     * @param array $options
     * @return $this
     */
    public function setSourceOption(array $options)
    {
        $this->_sourceOption      = $options;
        $this->_resolvedGeneralId = null;

        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setDestinationOptions(array $options)
    {
        $this->_destinationOptions = $options;
        $this->_resolvedGeneralId  = null;

        return $this;
    }

    // ---------------------------------------

    /**
     * @param array $matchedAttributes
     * @return $this
     */
    public function setMatchedAttributes(array $matchedAttributes)
    {
        $this->_matchedAttributes = $matchedAttributes;
        return $this;
    }

    //########################################

    /**
     * @return $this
     */
    public function resolve()
    {
        foreach ($this->_destinationOptions as $generalId => $destinationOption) {
            if (count($this->_sourceOption) != count($destinationOption)) {
                continue;
            }

            $isResolved = false;

            foreach ($destinationOption as $destinationAttribute => $destinationOptionNames) {
                $sourceAttribute = array_search($destinationAttribute, $this->_matchedAttributes);
                $sourceOptionNames = $this->_sourceOption[$sourceAttribute];

                $diff = array_intersect((array)$sourceOptionNames, (array)$destinationOptionNames);
                if (!empty($diff)) {
                    $isResolved = true;
                    continue;
                }

                $isResolved = false;
                break;
            }

            if ($isResolved) {
                $this->_resolvedGeneralId = $generalId;
                break;
            }
        }

        return $this;
    }

    public function getResolvedGeneralId()
    {
        return $this->_resolvedGeneralId;
    }

    //########################################
}
