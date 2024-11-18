<?php

abstract class Ess_M2ePro_Model_Amazon_Listing_Product_RetrieveIdentifiers_AbstractIdentifier
{
    /** @var string */
    protected $identifier;

    /**
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return bool
     */
    abstract public function hasResolvedType();

    /**
     * @return bool
     */
    public function hasUnresolvedType()
    {
        return !$this->hasResolvedType();
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function __toString()
    {
        return $this->getIdentifier();
    }
}
