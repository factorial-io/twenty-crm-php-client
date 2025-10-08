<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Unit\DTO;

use Factorial\TwentyCrm\DTO\DomainName;
use Factorial\TwentyCrm\DTO\DomainNameCollection;
use PHPUnit\Framework\TestCase;

class DomainNameTest extends TestCase
{
    public function testCreateDomainName(): void
    {
        $domain = new DomainName('example.com');

        $this->assertEquals('example.com', $domain->getDomain());
        $this->assertEquals('https://example.com', $domain->getUrl());
        $this->assertEquals('example.com', $domain->getPlainDomain());
    }

    public function testDomainNameWithProtocol(): void
    {
        $domain = new DomainName('https://example.com');

        $this->assertEquals('https://example.com', $domain->getDomain());
        $this->assertEquals('https://example.com', $domain->getUrl());
        $this->assertEquals('example.com', $domain->getPlainDomain());
    }

    public function testDomainNameWithHttpProtocol(): void
    {
        $domain = new DomainName('http://example.com');

        $this->assertEquals('http://example.com', $domain->getDomain());
        $this->assertEquals('http://example.com', $domain->getUrl());
        $this->assertEquals('example.com', $domain->getPlainDomain());
    }

    public function testDomainNameFromArray(): void
    {
        $domain = DomainName::fromArray(['domain' => 'example.com']);

        $this->assertEquals('example.com', $domain->getDomain());
    }

    public function testDomainNameFromArrayWithUrl(): void
    {
        $domain = DomainName::fromArray(['url' => 'https://example.com']);

        $this->assertEquals('https://example.com', $domain->getDomain());
    }

    public function testDomainNameToArray(): void
    {
        $domain = new DomainName('example.com');

        $this->assertEquals(['domain' => 'example.com'], $domain->toArray());
    }

    public function testDomainNameSetters(): void
    {
        $domain = new DomainName();
        $domain->setDomain('test.com');

        $this->assertEquals('test.com', $domain->getDomain());
    }

    public function testDomainNameWithNull(): void
    {
        $domain = new DomainName();

        $this->assertNull($domain->getDomain());
        $this->assertNull($domain->getUrl());
        $this->assertNull($domain->getPlainDomain());
    }

    // DomainNameCollection tests

    public function testDomainNameCollectionCreate(): void
    {
        $primaryDomain = new DomainName('primary.com');
        $additionalDomain = new DomainName('secondary.com');

        $collection = new DomainNameCollection($primaryDomain, [$additionalDomain]);

        $this->assertSame($primaryDomain, $collection->getPrimaryDomainName());
        $this->assertCount(1, $collection->getAdditionalDomainNames());
        $this->assertCount(2, $collection->getAllDomainNames());
    }

    public function testDomainNameCollectionFromArray(): void
    {
        $data = [
          'primaryLinkUrl' => 'https://primary.com',
          'primaryLinkLabel' => 'primary.com',
          'secondaryLinks' => [
            ['url' => 'https://secondary1.com', 'label' => 'secondary1.com'],
            ['url' => 'https://secondary2.com', 'label' => 'secondary2.com'],
          ],
        ];

        $collection = DomainNameCollection::fromArray($data);

        $this->assertEquals('https://primary.com', $collection->getPrimaryDomainName()->getDomain());
        $this->assertCount(2, $collection->getAdditionalDomainNames());

        $additional = $collection->getAdditionalDomainNames();
        $this->assertEquals('https://secondary1.com', $additional[0]->getDomain());
        $this->assertEquals('https://secondary2.com', $additional[1]->getDomain());
    }

    public function testDomainNameCollectionFromArrayWithStringLinks(): void
    {
        $data = [
          'primaryLinkUrl' => 'https://primary.com',
          'secondaryLinks' => ['https://secondary.com'],
        ];

        $collection = DomainNameCollection::fromArray($data);

        $this->assertEquals('https://primary.com', $collection->getPrimaryDomainName()->getDomain());
        $this->assertCount(1, $collection->getAdditionalDomainNames());
    }

    public function testDomainNameCollectionToArray(): void
    {
        $primaryDomain = new DomainName('primary.com');
        $additionalDomain1 = new DomainName('secondary1.com');
        $additionalDomain2 = new DomainName('https://secondary2.com');

        $collection = new DomainNameCollection($primaryDomain, [$additionalDomain1, $additionalDomain2]);

        $result = $collection->toArray();

        $this->assertEquals('https://primary.com', $result['primaryLinkUrl']);
        $this->assertEquals('primary.com', $result['primaryLinkLabel']);
        $this->assertCount(2, $result['secondaryLinks']);
        $this->assertEquals('https://secondary1.com', $result['secondaryLinks'][0]['url']);
        $this->assertEquals('secondary1.com', $result['secondaryLinks'][0]['label']);
        $this->assertEquals('https://secondary2.com', $result['secondaryLinks'][1]['url']);
        $this->assertEquals('secondary2.com', $result['secondaryLinks'][1]['label']);
    }

    public function testDomainNameCollectionIsEmpty(): void
    {
        $collection = new DomainNameCollection();

        $this->assertTrue($collection->isEmpty());
    }

    public function testDomainNameCollectionIsNotEmpty(): void
    {
        $collection = new DomainNameCollection(new DomainName('example.com'));

        $this->assertFalse($collection->isEmpty());
    }

    public function testDomainNameCollectionGetPrimaryDomain(): void
    {
        $collection = new DomainNameCollection(new DomainName('example.com'));

        $this->assertEquals('example.com', $collection->getPrimaryDomain());
    }

    public function testDomainNameCollectionGetPrimaryDomainFallback(): void
    {
        $collection = new DomainNameCollection(null, [new DomainName('fallback.com')]);

        $this->assertEquals('fallback.com', $collection->getPrimaryDomain());
    }

    public function testDomainNameCollectionGetPrimaryUrl(): void
    {
        $collection = new DomainNameCollection(new DomainName('example.com'));

        $this->assertEquals('https://example.com', $collection->getPrimaryUrl());
    }

    public function testDomainNameCollectionAddAdditionalDomainName(): void
    {
        $collection = new DomainNameCollection(new DomainName('primary.com'));
        $collection->addAdditionalDomainName(new DomainName('additional.com'));

        $this->assertCount(1, $collection->getAdditionalDomainNames());
        $this->assertCount(2, $collection->getAllDomainNames());
    }

    public function testDomainNameCollectionSetters(): void
    {
        $collection = new DomainNameCollection();

        $primary = new DomainName('primary.com');
        $collection->setPrimaryDomainName($primary);

        $this->assertSame($primary, $collection->getPrimaryDomainName());

        $additionals = [new DomainName('add1.com'), new DomainName('add2.com')];
        $collection->setAdditionalDomainNames($additionals);

        $this->assertCount(2, $collection->getAdditionalDomainNames());
    }
}
