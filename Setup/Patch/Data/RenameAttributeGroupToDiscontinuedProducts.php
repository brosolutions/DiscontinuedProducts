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

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Renames the pre-existing "Replacement Products" attribute group to "Discontinued Products"
 * to match the module rename. Only the display name changes - the group code stays
 * "replacement-products" since Ui\DataProvider\Product\Form\Modifier\Replacement looks the
 * group up by that code.
 *
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class RenameAttributeGroupToDiscontinuedProducts implements DataPatchInterface
{
    /**
     * @var string
     */
    private const OLD_ATTRIBUTE_GROUP_NAME = 'Replacement Products';

    /**
     * @var string
     */
    private const NEW_ATTRIBUTE_GROUP_NAME = 'Discontinued Products';

    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $connection = $this->moduleDataSetup->getConnection();
        $groupTable = $this->moduleDataSetup->getTable('eav_attribute_group');
        $setTable = $this->moduleDataSetup->getTable('eav_attribute_set');

        $setIds = $connection->fetchCol(
            $connection->select()->from($setTable, 'attribute_set_id')->where('entity_type_id = ?', $entityTypeId)
        );

        if ($setIds) {
            $connection->update(
                $groupTable,
                ['attribute_group_name' => self::NEW_ATTRIBUTE_GROUP_NAME],
                [
                    'attribute_group_name = ?' => self::OLD_ATTRIBUTE_GROUP_NAME,
                    'attribute_set_id IN (?)' => $setIds,
                ]
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [
            CreateReplacementProductsAttributeGroup::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
