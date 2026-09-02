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
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class CreateDiscontinuedRedirectAttributes implements DataPatchInterface
{
    /**
     * @var string
     */
    public const CODE_REDIRECT_ENABLED = 'discontinued_redirect_enabled';

    /**
     * @var string
     */
    public const CODE_REDIRECT_SKU = 'discontinued_redirect_sku';

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

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            self::CODE_REDIRECT_ENABLED,
            [
                'type' => 'int',
                'label' => 'Redirect to Another Product',
                'input' => 'boolean',
                'default' => 0,
                'visible' => 1,
                'user_defined' => 1,
                'global' => 1,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            self::CODE_REDIRECT_SKU,
            [
                'type' => 'varchar',
                'label' => 'Redirect to SKU',
                'input' => 'text',
                'visible' => 1,
                'required' => false,
                'user_defined' => 1,
                'global' => 1,
            ]
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [
            CreateProductReplacementAttributes::class,
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
