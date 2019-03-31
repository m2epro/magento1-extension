<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class M2eProChangesCatcher extends Magmi_ItemProcessor
{
    const INSTRUCTION_TYPE_PRODUCT_CHANGED = 'magmi_plugin_product_changed';
    const INSTRUCTION_INITIATOR            = 'public_services_magmi_plugin';

    protected $changedMagentoProductsIds = array();

    protected $statistics = array(
        'total_magento_products'      => 0,
        'total_listings_products'     => 0,
        'processed_listings_products' => 0,
    );

    //########################################

    public function initialize($params) {}

    /**
     * @return array
     */
    public function getPluginInfo()
    {
        return array(
            "name"    => "Ess M2ePro Product Changes Inspector",
            "author"  => "ESS",
            "version" => "2.1.0",
            "url"     => "https://docs.m2epro.com/x/CQ1PAQ"
        );
    }

    //########################################

    public function processItemAfterId(&$item, $params = null)
    {
        $this->changedMagentoProductsIds[] = (int)$params['product_id'];
        return true;
    }

    /**
     * @return bool
     */
    public function endImport()
    {
        $this->processChanges();

        $this->saveStatistics();
        $this->resetStatistics();

        return true;
    }

    //########################################

    private function getChangedListingsProductsData()
    {
        if (empty($this->changedMagentoProductsIds)) {
            return array();
        }

        $listingProductTable  = $this->tablename('m2epro_listing_product');
        $variationTable       = $this->tablename('m2epro_listing_product_variation');
        $variationOptionTable = $this->tablename('m2epro_listing_product_variation_option');

        $listingsProductsData = array();

        foreach (array_chunk($this->changedMagentoProductsIds, 1000) as $changedMagentoProductsIdsPart) {

            $productsIds = implode(',', $changedMagentoProductsIdsPart);
            $stmt = $this->select(<<<SQL
SELECT DISTINCT `id` AS `listing_product_id`, `component_mode`
FROM `{$listingProductTable}`
WHERE `product_id` IN ({$productsIds})

UNION

SELECT DISTINCT `lpv`.`listing_product_id`, `lpv`.`component_mode` FROM `{$variationOptionTable}` AS `lpvo`
INNER JOIN `{$variationTable}` AS `lpv` ON `lpv`.`id` = `lpvo`.`listing_product_variation_id`
WHERE `lpvo`.`product_id` IN ({$productsIds})
SQL
            );

            while ($row = $stmt->fetch()) {

                $listingProductId = (int)$row['listing_product_id'];

                $listingsProductsData[$listingProductId] = array(
                    'listing_product_id' => $listingProductId,
                    'component'          => $row['component_mode'],
                );
            }
        }

        return $listingsProductsData;
    }

    private function filterAlreadyProcessedListingsProducts(array $listingsProductsData)
    {
        $instructionTable = $this->tableName('m2epro_listing_product_instruction');

        foreach (array_chunk(array_keys($listingsProductsData), 1000) as $listingsProductsIdsPart) {

            $productsIds = implode(',', $listingsProductsIdsPart);
            $stmt = $this->select(<<<SQL
SELECT DISTINCT `listing_product_id` FROM `{$instructionTable}`
WHERE `listing_product_id` IN ($productsIds) AND `type` = ?
SQL
                , array(self::INSTRUCTION_TYPE_PRODUCT_CHANGED)
            );

            while ($row = $stmt->fetch()) {
                $lpId = (int)$row['listing_product_id'];
                unset($listingsProductsData[$lpId]);
            }
        }

        return $listingsProductsData;
    }

    private function processChanges()
    {
        $this->statistics['total_magento_products'] = count($this->changedMagentoProductsIds);
        if (count($this->changedMagentoProductsIds) == 0) {
            return true;
        }

        $listingsProductsData = $this->getChangedListingsProductsData();
        $this->statistics['total_listings_products'] = count($listingsProductsData);

        if (empty($listingsProductsData)) {
            return true;
        }

        $notProcessedListingsProductsData = $this->filterAlreadyProcessedListingsProducts($listingsProductsData);
        $this->statistics['processed_listings_products'] = count($notProcessedListingsProductsData);

        if (empty($notProcessedListingsProductsData)) {
            return true;
        }

        $currentDateTime = new DateTime('now', new DateTimeZone('UTC'));
        $instructionTable = $this->tablename('m2epro_listing_product_instruction');

        $insertSql = "INSERT INTO `{$instructionTable}`
                      (`listing_product_id`,`component`,`type`,`initiator`,`priority`,`create_date`)
                      VALUES (?,?,?,?,?,?)";

        foreach ($notProcessedListingsProductsData as $listingProductData) {
            $instructionData = array(
                'listing_product_id' => $listingProductData['listing_product_id'],
                'component'          => $listingProductData['component'],
                'type'               => self::INSTRUCTION_TYPE_PRODUCT_CHANGED,
                'initiator'          => self::INSTRUCTION_INITIATOR,
                'priority'           => 50,
                'create_date'        => $currentDateTime->format('Y-m-d H:i:s'),
            );

            $this->insert($insertSql, array_values($instructionData));
        }
    }

    //########################################

    protected function resetStatistics()
    {
        $this->statistics = array(
            'total_magento_products'      => 0,
            'total_listings_products'     => 0,
            'processed_listings_products' => 0,
        );
    }

    protected function saveStatistics()
    {
        $messages = array();

        $messages[] = "Total Magento Products: {$this->statistics['total_magento_products']}.";
        $messages[] = "Total Listings Products: {$this->statistics['total_listings_products']}.";
        $messages[] = "Processed Listings Products: {$this->statistics['processed_listings_products']}.";

        $this->log(implode(' ## ', $messages));
    }

    //########################################
}