<?php

class Ess_M2ePro_Model_Amazon_Dictionary_ProductType extends Ess_M2ePro_Model_Abstract
{
    /**
     * @var array
     */
    private $flatScheme;

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Dictionary_ProductType');
    }

    public function create(
        Ess_M2ePro_Model_Marketplace $marketplace,
        $nick,
        $title,
        array $schema,
        array $variationThemes,
        array $attributesGroups,
        \DateTime $serverUpdateDate,
        \DateTime $clientUpdateDate
    ) {
        $this->setData(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_MARKETPLACE_ID,
            (int)$marketplace->getId()
        )
            ->setData(Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_NICK, $nick)
            ->setData(Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_TITLE, $title)
            ->setScheme($schema)
            ->setVariationThemes($variationThemes)
            ->setAttributesGroups($attributesGroups)
            ->setClientDetailsLastUpdateDate($clientUpdateDate)
            ->setServerDetailsLastUpdateDate($serverUpdateDate);

        return $this;
    }

    // ----------------------------------------

    public function getMarketplaceId()
    {
        return (int)$this->getData(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_MARKETPLACE_ID
        );
    }

    public function getNick()
    {
        return (string)$this->getData(Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_NICK);
    }

    public function getTitle()
    {
        return (string)$this->getData(Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_TITLE);
    }

    public function setScheme(array $schema)
    {
        $this->setData(Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_SCHEME, json_encode($schema));

        return $this;
    }

    public function getScheme()
    {
        $value = $this->getData(Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_SCHEME);
        if (empty($value)) {
            return array();
        }

        return (array)json_decode($value, true);
    }

    public function setVariationThemes(array $variationThemes)
    {
        $this->setData(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_VARIATION_THEMES,
            json_encode($variationThemes)
        );

        return $this;
    }

    public function hasVariationThemes()
    {
        $variationThemes = $this->getVariationThemes();

        return !empty($variationThemes);
    }

    public function getVariationThemes()
    {
        $value = $this->getData(Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_VARIATION_THEMES);
        if (empty($value)) {
            return array();
        }

        return (array)json_decode($value, true);
    }

    public function hasVariationTheme($variationTheme)
    {
        $variationThemes = $this->getVariationThemes();

        return isset($variationThemes[$variationTheme]);
    }

    public function getVariationThemesAttributes($variationTheme)
    {
        $variationThemesAttributes = array();
        $variationThemes = $this->getVariationThemes();

        if ($variationThemes[$variationTheme]['attributes']) {
            $variationThemesAttributes = $variationThemes[$variationTheme]['attributes'];
        }
        return $variationThemesAttributes;
    }

    public function setAttributesGroups(array $attributesGroups)
    {
        $this->setData(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_ATTRIBUTES_GROUP,
            json_encode($attributesGroups)
        );

        return $this;
    }

    public function getAttributesGroups()
    {
        $value = $this->getData(Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_ATTRIBUTES_GROUP);
        if (empty($value)) {
            return array();
        }

        return (array)json_decode($value, true);
    }

    public function getClientDetailsLastUpdateDate()
    {
        return Mage::helper('M2ePro')->createGmtDateTime(
            $this->getData(
                Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_CLIENT_DETAILS_LAST_UPDATE_DATE
            )
        );
    }

    public function setClientDetailsLastUpdateDate(\DateTime $value)
    {
        $this->setData(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_CLIENT_DETAILS_LAST_UPDATE_DATE,
            $value->format('Y-m-d H:i:s')
        );

        return $this;
    }

    public function getServerDetailsLastUpdateDate()
    {
        return Mage::helper('M2ePro')->createGmtDateTime(
            $this->getData(
                Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_SERVER_DETAILS_LAST_UPDATE_DATE
            )
        );
    }

    public function setServerDetailsLastUpdateDate(\DateTime $value)
    {
        $this->setData(
            Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_SERVER_DETAILS_LAST_UPDATE_DATE,
            $value->format('Y-m-d H:i:s')
        );

        return $this;
    }

    public function isInvalid()
    {
        return (bool)$this->getData(Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_INVALID);
    }

    public function markAsInvalid()
    {
        $this->setData(Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_INVALID, (int)true);

        return $this;
    }

    public function markAsValid()
    {
        $this->setData(Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType::COLUMN_INVALID, (int)false);

        return $this;
    }

    /**
     * @param $path
     * @return Ess_M2ePro_Model_Amazon_ProductType_Validator_ValidatorInterface|
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getValidatorByPath($path)
    {
        $flatScheme = $this->getFlatScheme();
        if (!array_key_exists($path, $flatScheme)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Not found specific path');
        }

        $validatorBuilderData = $flatScheme[$path];
        $validatorBuilderData['group_title'] = $this->getGroupTitleByNick($validatorBuilderData['group_nick']);
        $validationBuilder = new Ess_M2ePro_Model_Amazon_ProductType_Validator_ValidatorBuilder($validatorBuilderData);

        return $validationBuilder->build();
    }

    // ----------------------------------------

    public function findNameByProductTypeCode($code)
    {
        $flatScheme = $this->getFlatScheme();

        if (!array_key_exists($code, $flatScheme)) {
            return '';
        }

        return $flatScheme[$code]['title'];
    }

    private function getFlatScheme()
    {
        if (!isset($this->flatScheme)) {
            $this->flatScheme = $this->convertSchemeToFlat($this->getScheme());
        }

        return $this->flatScheme;
    }

    private function convertSchemeToFlat(array $array, array $parentAttributes = array())
    {
        $result = array();
        foreach ($array as $item) {
            if (!empty($parentAttributes)) {
                if ($parentAttributes['title'] !== $item['title']) {
                    $item['title'] = $parentAttributes['title'] . ' >> ' . $item['title'];
                }
                $item['name'] = $parentAttributes['name'] . '/' . $item['name'];
            }

            if (array_key_exists('children', $item) && $item['children'] && $item['type'] !== null) {
                $result += $this->convertSchemeToFlat(
                    $item['children'],
                    array(
                        'name' => $item['name'],
                        'title' => $item['title'],
                    )
                );
                continue;
            }

            $result[$item['name']] = $item;
        }

        return $result;
    }

    private function getGroupTitleByNick($groupNick)
    {
        $attributesGroups = $this->getAttributesGroups();

        return !empty($attributesGroups[$groupNick]) ? $attributesGroups[$groupNick] : '';
    }
}