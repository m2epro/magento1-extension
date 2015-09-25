<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Action_RequestData
{
    /**
     * @var array
     */
    protected $data = array();

    // ########################################

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    // ########################################

    public function hasQty()
    {
        return isset($this->data['qty']);
    }

    public function hasPrice()
    {
        return $this->hasPriceFixed();
    }

    // ----------------------------------------

    public function hasPriceFixed()
    {
        return isset($this->data['price_fixed']);
    }

    // ----------------------------------------

    public function hasTitle()
    {
        return isset($this->data['title']);
    }

    public function hasSubtitle()
    {
        return isset($this->data['subtitle']);
    }

    public function hasDescription()
    {
        return isset($this->data['description']);
    }

    // ########################################

    public function getQty()
    {
        return $this->hasQty() ? $this->data['qty'] : NULL;
    }

    public function getPriceFixed()
    {
        return $this->hasPriceFixed() ? $this->data['price_fixed'] : NULL;
    }

    // ----------------------------------------

    public function getTitle()
    {
        return $this->hasTitle() ? $this->data['title'] : NULL;
    }

    public function getSubtitle()
    {
        return $this->hasSubtitle() ? $this->data['subtitle'] : NULL;
    }

    public function getDescription()
    {
        return $this->hasDescription() ? $this->data['description'] : NULL;
    }

    // ########################################
}