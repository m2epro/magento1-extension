<?php

class Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Category_ProductType
{
    /** @var string */
    private $title;
    /** @var string */
    private $nick;

    /**
     * @param string $title
     * @param string $nick
     */
    public function __construct($title, $nick)
    {
        $this->title = $title;
        $this->nick = $nick;
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
}
