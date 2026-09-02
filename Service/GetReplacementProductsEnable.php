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

namespace BroSolutions\DiscontinuedProducts\Service;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class GetReplacementProductsEnable
{
    /**
     * @var string
     */
    private const REPLACEMENT_CONFIG_PATH = 'brosolutions_discontinued_products/general/enable';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get replacement enable
     *
     * @return bool
     */
    public function execute(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::REPLACEMENT_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }
}
