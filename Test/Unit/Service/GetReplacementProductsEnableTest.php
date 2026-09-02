<?php
/**
 * Copyright (c) 2026 BroSolutions
 * All rights reserved
 */
declare(strict_types=1);

namespace BroSolutions\DiscontinuedProducts\Test\Unit\Service;

use BroSolutions\DiscontinuedProducts\Service\GetReplacementProductsEnable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetReplacementProductsEnableTest extends TestCase
{
    private const CONFIG_PATH = 'brosolutions_discontinued_products/general/enable';

    /** @var ScopeConfigInterface|MockObject */
    private $scopeConfig;

    /** @var GetReplacementProductsEnable */
    private GetReplacementProductsEnable $service;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->service = new GetReplacementProductsEnable($this->scopeConfig);
    }

    public function testReturnsTrueWhenEnabled(): void
    {
        $this->scopeConfig->method('getValue')
            ->with(self::CONFIG_PATH, ScopeInterface::SCOPE_STORE)
            ->willReturn('1');

        $this->assertTrue($this->service->execute());
    }

    public function testReturnsFalseWhenDisabled(): void
    {
        $this->scopeConfig->method('getValue')
            ->with(self::CONFIG_PATH, ScopeInterface::SCOPE_STORE)
            ->willReturn('0');

        $this->assertFalse($this->service->execute());
    }

    public function testReturnsFalseWhenNull(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturn(null);

        $this->assertFalse($this->service->execute());
    }
}
