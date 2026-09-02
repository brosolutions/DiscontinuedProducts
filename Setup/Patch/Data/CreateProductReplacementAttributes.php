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
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class CreateProductReplacementAttributes implements DataPatchInterface
{
    /**
     * @var string
     */
    private const CODE_PRODUCT_IS_DISCONTINUED = 'is_discontinued';

    /**
     * @var string
     */
    private const CODE_REPCALEMENT_PRODUCTS = 'replacement_products';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

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

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);

        $eavSetup->addAttribute(
            Product::ENTITY,
            self::CODE_PRODUCT_IS_DISCONTINUED,
            [
                'type' => 'int',
                'label' => 'Product Discontinued',
                'input' => 'boolean',
                'default' => 0,
                'visible' => 1,
                'user_defined' => 1,
                'global' => 1
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            self::CODE_REPCALEMENT_PRODUCTS,
            [
                'type' => 'text',
                'label' => 'Replacement Products',
                'input' => 'text',
                'backend_model' => ArrayBackend::class,
                'visible' => false,
                'user_defined' => 1,
                'global' => 1
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
