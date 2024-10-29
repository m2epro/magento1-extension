<?php

use Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType as DictionaryResource;

class Ess_M2ePro_Model_Walmart_Dictionary_ProductType extends Ess_M2ePro_Model_Abstract
{
    /**
     * @param int $marketplaceId
     * @param string $nick
     * @param string $title
     * @param array $attributes
     * @param string[] $variationAttributes
     * @return void
     */
    public function init(
        $marketplaceId,
        $nick,
        $title,
        array $attributes,
        array $variationAttributes
    ) {
        $this->setData(DictionaryResource::COLUMN_MARKETPLACE_ID, $marketplaceId);
        $this->setData(DictionaryResource::COLUMN_NICK, $nick);
        $this->setData(DictionaryResource::COLUMN_TITLE, $title);
        $this->setData(
            DictionaryResource::COLUMN_ATTRIBUTES,
            json_encode($attributes)
        );
        $this->setData(
            DictionaryResource::COLUMN_VARIATION_ATTRIBUTES,
            json_encode($variationAttributes)
        );
        $this->markAsValid();
    }
    
    public function _construct()
    {
        parent::_construct();

        $this->_init('M2ePro/Walmart_Dictionary_ProductType');
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        if ($this->getData(DictionaryResource::COLUMN_ID) === null) {
            return null;
        }

        return (int)$this->getData(DictionaryResource::COLUMN_ID);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(DictionaryResource::COLUMN_TITLE);
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return $this->getData(DictionaryResource::COLUMN_NICK);
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData(DictionaryResource::COLUMN_MARKETPLACE_ID);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        $attributes = $this->getData(DictionaryResource::COLUMN_ATTRIBUTES);
        if (empty($attributes)) {
            return array();
        }

        return json_decode($attributes, true);
    }

    /**
     * @return string[]
     */
    public function getVariationAttributes()
    {
        $variationAttributes = $this->getData(DictionaryResource::COLUMN_VARIATION_ATTRIBUTES);
        if (empty($variationAttributes)) {
            return array();
        }

        return json_decode($variationAttributes, true);
    }

    /**
     * @return bool
     */
    public function isInvalid()
    {
        return (bool)$this->getData(DictionaryResource::COLUMN_INVALID);
    }

    /**
     * @return $this
     */
    public function markAsInvalid()
    {
        $this->setIsInvalid(true);

        return $this;
    }

    /**
     * @return $this
     */
    public function markAsValid()
    {
        $this->setIsInvalid(false);

        return $this;
    }

    /**
     * @param bool $val
     * @return void
     */
    private function setIsInvalid($val)
    {
        $this->setData(DictionaryResource::COLUMN_INVALID, $val);
    }
}
