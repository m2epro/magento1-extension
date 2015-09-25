<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute_Resolver
{
    private $sourceAttributes = array();

    private $sourceAttributesNames = array();

    private $destinationAttributes = array();

    private $destinationAttributesNames = array();

    private $resolvedAttributes = array();

    // ##########################################################

    public function addSourceAttribute($attribute, array $names)
    {
        if (in_array($attribute, $this->sourceAttributes)) {
            return $this;
        }

        $this->sourceAttributes[] = $attribute;
        $this->sourceAttributesNames[$attribute] = $names;

        return $this;
    }

    public function addDestinationAttribute($attribute, array $names)
    {
        if (in_array($attribute, $this->destinationAttributes)) {
            return $this;
        }

        $this->destinationAttributes[] = $attribute;
        $this->destinationAttributesNames[$attribute] = $names;

        return $this;
    }

    // ##########################################################

    public function resolve()
    {
        if (array_diff($this->sourceAttributes, array_keys($this->resolvedAttributes))) {
            $this->resolvedAttributes = array();
        }

        foreach ($this->sourceAttributes as $sourceAttribute) {

            if (!empty($this->resolvedAttributes[$sourceAttribute]) &&
                in_array($this->resolvedAttributes[$sourceAttribute], $this->destinationAttributes)
            ) {
                continue;
            }

            $this->resolvedAttributes[$sourceAttribute] = null;

            $sourceNames = $this->sourceAttributesNames[$sourceAttribute];

            foreach ($this->destinationAttributes as $destinationAttribute) {
                $destinationNames = $this->destinationAttributesNames[$destinationAttribute];

                if (count(array_intersect($sourceNames, $destinationNames)) > 0 &&
                    !in_array($destinationAttribute, $this->resolvedAttributes)
                ) {
                    $this->resolvedAttributes[$sourceAttribute] = $destinationAttribute;
                    break;
                }
            }
        }

        return $this;
    }

    public function getResolvedAttributes()
    {
        return $this->resolvedAttributes;
    }

    // ##########################################################

    public function clearSourceAttributes()
    {
        $this->sourceAttributes = array();
        $this->sourceAttributesNames = array();

        return $this;
    }

    public function clearDestinationAttributes()
    {
        $this->destinationAttributes = array();
        $this->destinationAttributesNames = array();

        return $this;
    }

    // ##########################################################
}