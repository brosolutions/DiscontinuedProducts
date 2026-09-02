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
 * Adds the redirect attributes to the same attribute group as "is_discontinued"
 * (group code "replacement-products", created by CreateReplacementProductsAttributeGroup).
 *
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class AddDiscontinuedRedirectAttributesToGroup implements DataPatchInterface
{
    /**
     * Looked up by CODE, not display name: the group's display name was changed to
     * "Discontinued Products" by RenameAttributeGroupToDiscontinuedProducts, but
     * EavSetup::getAttributeGroupId() resolves a non-numeric argument by converting it to
     * a group CODE (slugified) and matching eav_attribute_group.attribute_group_code - which
     * still reads "replacement-products" (deliberately left unchanged, see
     * RenameAttributeGroupToDiscontinuedProducts). Passing the display name here would not
     * match and would silently fall back to the default "General" group instead.
     *
     * @var string
     */
    private const ATTRIBUTE_GROUP_CODE = 'replacement-products';

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
        $allAttributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);

        $attributes = [
            CreateDiscontinuedRedirectAttributes::CODE_REDIRECT_ENABLED => 20,
            CreateDiscontinuedRedirectAttributes::CODE_REDIRECT_SKU => 30,
        ];

        foreach ($allAttributeSetIds as $attributeSetId) {
            $groupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, self::ATTRIBUTE_GROUP_CODE);
            if (!$groupId) {
                continue;
            }

            foreach ($attributes as $attributeCode => $sortOrder) {
                if (!$eavSetup->getAttributeId($entityTypeId, $attributeCode)) {
                    continue;
                }

                $eavSetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $groupId,
                    $attributeCode,
                    $sortOrder
                );
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [
            CreateDiscontinuedRedirectAttributes::class,
            CreateReplacementProductsAttributeGroup::class,
            RenameAttributeGroupToDiscontinuedProducts::class,
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
