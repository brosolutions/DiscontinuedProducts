<?php
/**
 * Copyright (c) 2026 BroSolutions
 * All rights reserved
 *
 * This product includes proprietary software developed at BroSolutions, Ukraine
 * For more information see https://www.brosolutions.net/
 *
 * To obtain a valid license for using this software please contact us at
 * contact@brosolutions.net
 */
declare(strict_types=1);

namespace BroSolutions\DiscontinuedProducts\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class AddReplacementLinkType implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @ingeritdoc
     */
    public function apply(): void
    {
        $connection = $this->moduleDataSetup->getConnection();

        $linkTypeTable = $this->moduleDataSetup->getTable('catalog_product_link_type');
        $linkAttrTable = $this->moduleDataSetup->getTable('catalog_product_link_attribute');

        $connection->startSetup();

        $existingId = $connection->fetchOne(
            "SELECT link_type_id FROM {$linkTypeTable} WHERE code = ?",
            ['replacement']
        );

        if ($existingId) {
            $connection->endSetup();
            return;
        }

        $nextLinkTypeId = (int) $connection->fetchOne(
            "SELECT MAX(link_type_id) + 1 FROM {$linkTypeTable}"
        );

        $connection->insert(
            $linkTypeTable,
            [
                'link_type_id' => $nextLinkTypeId,
                'code' => 'replacement',
            ]
        );

        $connection->insert(
            $linkAttrTable,
            [
                'link_type_id' => $nextLinkTypeId,
                'product_link_attribute_code' => 'position',
                'data_type' => 'int',
            ]
        );

        $connection->endSetup();
    }

    /**
     * @ingeritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @ingeritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
