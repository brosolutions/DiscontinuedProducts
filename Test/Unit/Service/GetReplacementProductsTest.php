<?php
/**
 * Copyright (c) 2026 BroSolutions
 * All rights reserved
 */
declare(strict_types=1);

namespace BroSolutions\DiscontinuedProducts\Test\Unit\Service;

use BroSolutions\DiscontinuedProducts\Service\GetReplacementProducts;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetReplacementProductsTest extends TestCase
{
    /** @var CollectionFactory|MockObject */
    private $collectionFactory;

    /** @var GetReplacementProducts */
    private GetReplacementProducts $service;

    protected function setUp(): void
    {
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->service = new GetReplacementProducts($this->collectionFactory);
    }

    public function testReturnsNullWhenProductIsNotDiscontinued(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getData')->with('is_discontinued')->willReturn(0);

        $this->collectionFactory->expects($this->never())->method('create');

        $this->assertNull($this->service->execute($product));
    }

    public function testReturnsUnfilteredCollectionWhenNoReplacementLinks(): void
    {
        $product = $this->createMock(Product::class);
        $product->method('getData')->with('is_discontinued')->willReturn(1);
        $product->method('getProductLinks')->willReturn([]);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->never())->method('addAttributeToFilter');
        $this->collectionFactory->method('create')->willReturn($collection);

        $this->assertSame($collection, $this->service->execute($product));
    }

    public function testIgnoresLinksThatAreNotOfTypeReplacement(): void
    {
        $relatedLink = new DataObject(['link_type' => 'related', 'linked_product_sku' => 'REL-1']);

        $product = $this->createMock(Product::class);
        $product->method('getData')->with('is_discontinued')->willReturn(1);
        $product->method('getProductLinks')->willReturn([$relatedLink]);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->never())->method('addAttributeToFilter');
        $this->collectionFactory->method('create')->willReturn($collection);

        $this->service->execute($product);
    }

    public function testFiltersCollectionByReplacementLinkSkus(): void
    {
        $replacementLink = new DataObject(['link_type' => 'replacement', 'linked_product_sku' => 'REPL-1']);
        $relatedLink = new DataObject(['link_type' => 'related', 'linked_product_sku' => 'REL-1']);

        $product = $this->createMock(Product::class);
        $product->method('getData')->with('is_discontinued')->willReturn(1);
        $product->method('getProductLinks')->willReturn([$replacementLink, $relatedLink]);
        $product->method('getStoreId')->willReturn(1);

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())->method('setStoreId')->with(1);
        $collection->expects($this->once())->method('addStoreFilter')->with(1);
        $collection->expects($this->once())->method('addAttributeToSelect')->with('*');
        $collection->expects($this->once())
            ->method('addAttributeToFilter')
            ->with('sku', ['in' => ['REPL-1']]);

        $this->collectionFactory->method('create')->willReturn($collection);

        $this->assertSame($collection, $this->service->execute($product));
    }
}
