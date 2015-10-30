<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Action_RequestData
{
    /**
     * @var array
     */
    protected $data = array();

    //########################################

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    //########################################

    /**
     * @return bool
     */
    public function hasQty()
    {
        return isset($this->data['qty']);
    }

    /**
     * @return bool
     */
    public function hasPrice()
    {
        return $this->hasPriceFixed();
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasPriceFixed()
    {
        return isset($this->data['price_fixed']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasTitle()
    {
        return isset($this->data['title']);
    }

    /**
     * @return bool
     */
    public function hasSubtitle()
    {
        return isset($this->data['subtitle']);
    }

    /**
     * @return bool
     */
    public function hasDescription()
    {
        return isset($this->data['description']);
    }

    //########################################

    public function getQty()
    {
        return $this->hasQty() ? $this->data['qty'] : NULL;
    }

    public function getPriceFixed()
    {
        return $this->hasPriceFixed() ? $this->data['price_fixed'] : NULL;
    }

    // ---------------------------------------

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

    //########################################
}