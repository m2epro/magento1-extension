<?php

class Ess_M2ePro_Model_Walmart_Connector_ProductType_GetInfo_Response
{
    /** @var string */
    private $title;
    /** @var string */
    private $nick;
    /** @var array */
    private $variationAttributes;
    /** @var array */
    private $attributes;

    /**
     * @param string $title
     * @param string $nick
     */
    public function __construct(
        $title,
        $nick,
        array $variationAttributes,
        array $attributes
    ) {
        $this->attributes = $attributes;
        $this->title = $title;
        $this->nick = $nick;
        $this->variationAttributes = $variationAttributes;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     * @return string[]
     */
    public function getVariationAttributes()
    {
        return $this->variationAttributes;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}