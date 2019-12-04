<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute_Resolver
{
    protected $_sourceAttributes = array();

    protected $_sourceAttributesNames = array();

    protected $_destinationAttributes = array();

    protected $_destinationAttributesNames = array();

    protected $_resolvedAttributes = array();

    //########################################

    /**
     * @param $attribute
     * @param array $names
     * @return $this
     */
    public function addSourceAttribute($attribute, array $names)
    {
        if (in_array($attribute, $this->_sourceAttributes)) {
            return $this;
        }

        $this->_sourceAttributes[]                = $attribute;
        $this->_sourceAttributesNames[$attribute] = $names;

        return $this;
    }

    /**
     * @param $attribute
     * @param array $names
     * @return $this
     */
    public function addDestinationAttribute($attribute, array $names)
    {
        if (in_array($attribute, $this->_destinationAttributes)) {
            return $this;
        }

        $this->_destinationAttributes[]                = $attribute;
        $this->_destinationAttributesNames[$attribute] = $names;

        return $this;
    }

    //########################################

    /**
     * @return $this
     */
    public function resolve()
    {
        if (array_diff($this->_sourceAttributes, array_keys($this->_resolvedAttributes))) {
            $this->_resolvedAttributes = array();
        }

        foreach ($this->_sourceAttributes as $sourceAttribute) {
            if (!empty($this->_resolvedAttributes[$sourceAttribute]) &&
                in_array($this->_resolvedAttributes[$sourceAttribute], $this->_destinationAttributes)
            ) {
                continue;
            }

            $this->_resolvedAttributes[$sourceAttribute] = null;

            $sourceNames = $this->_sourceAttributesNames[$sourceAttribute];

            foreach ($this->_destinationAttributes as $destinationAttribute) {
                $destinationNames = $this->_destinationAttributesNames[$destinationAttribute];
                $diff = array_intersect($sourceNames, $destinationNames);

                if (!empty($diff) && !in_array($destinationAttribute, $this->_resolvedAttributes)) {
                    $this->_resolvedAttributes[$sourceAttribute] = $destinationAttribute;
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getResolvedAttributes()
    {
        return $this->_resolvedAttributes;
    }

    //########################################

    /**
     * @return $this
     */
    public function clearSourceAttributes()
    {
        $this->_sourceAttributes      = array();
        $this->_sourceAttributesNames = array();

        return $this;
    }

    /**
     * @return $this
     */
    public function clearDestinationAttributes()
    {
        $this->_destinationAttributes      = array();
        $this->_destinationAttributesNames = array();

        return $this;
    }

    //########################################
}
