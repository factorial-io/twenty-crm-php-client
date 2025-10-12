<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Unit\DTO;

use Factorial\TwentyCrm\DTO\Email;
use Factorial\TwentyCrm\Collection\EmailCollection;
use Factorial\TwentyCrm\Tests\TestCase;

class EmailTest extends TestCase
{
    public function testCreateEmail(): void
    {
        $email = new Email('john@example.com', true);

        $this->assertEquals('john@example.com', $email->getEmail());
        $this->assertTrue($email->isPrimary());
    }

    public function testCreateEmailNotPrimary(): void
    {
        $email = new Email('jane@example.com');

        $this->assertEquals('jane@example.com', $email->getEmail());
        $this->assertFalse($email->isPrimary());
    }

    public function testEmailFromString(): void
    {
        $email = Email::fromString('test@example.com', true);

        $this->assertEquals('test@example.com', $email->getEmail());
        $this->assertTrue($email->isPrimary());
    }

    public function testEmailSetters(): void
    {
        $email = new Email('old@example.com');

        $email->setEmail('new@example.com')
            ->setPrimary(true);

        $this->assertEquals('new@example.com', $email->getEmail());
        $this->assertTrue($email->isPrimary());
    }

    public function testEmailToString(): void
    {
        $email = new Email('john@example.com');

        $this->assertEquals('john@example.com', (string) $email);
    }

    public function testEmailCollectionCreate(): void
    {
        $collection = new EmailCollection('primary@example.com', ['additional@example.com']);

        $this->assertEquals('primary@example.com', $collection->getPrimaryEmail());
        $this->assertEquals(['additional@example.com'], $collection->getAdditionalEmails());
        $this->assertCount(2, $collection->getAllEmails());
    }

    public function testEmailCollectionFromArray(): void
    {
        $data = [
            'primaryEmail' => 'john@example.com',
            'additionalEmails' => ['jane@example.com', 'support@example.com'],
        ];

        $collection = EmailCollection::fromArray($data);

        $this->assertEquals('john@example.com', $collection->getPrimaryEmail());
        $this->assertEquals(['jane@example.com', 'support@example.com'], $collection->getAdditionalEmails());
        $this->assertCount(3, $collection->getAllEmails());
    }

    public function testEmailCollectionFromArrayWithNulls(): void
    {
        $data = [
            'primaryEmail' => 'test@example.com',
            'additionalEmails' => null,
        ];

        $collection = EmailCollection::fromArray($data);

        $this->assertEquals('test@example.com', $collection->getPrimaryEmail());
        $this->assertEmpty($collection->getAdditionalEmails());
    }

    public function testEmailCollectionFromArrayFiltersEmpty(): void
    {
        $data = [
            'primaryEmail' => 'test@example.com',
            'additionalEmails' => ['valid@example.com', '', null, 'another@example.com'],
        ];

        $collection = EmailCollection::fromArray($data);

        $this->assertEquals(['valid@example.com', 'another@example.com'], $collection->getAdditionalEmails());
    }

    public function testEmailCollectionToArray(): void
    {
        $collection = new EmailCollection('primary@example.com', ['additional@example.com']);

        $array = $collection->toArray();

        $this->assertEquals('primary@example.com', $array['primaryEmail']);
        $this->assertEquals(['additional@example.com'], $array['additionalEmails']);
    }

    public function testEmailCollectionToArrayOmitsEmpty(): void
    {
        $collection = new EmailCollection();

        $array = $collection->toArray();

        $this->assertEmpty($array);
        $this->assertArrayNotHasKey('primaryEmail', $array);
        $this->assertArrayNotHasKey('additionalEmails', $array);
    }

    public function testEmailCollectionAll(): void
    {
        $collection = new EmailCollection('primary@example.com', ['additional@example.com']);

        $all = $collection->all();

        $this->assertCount(2, $all);
        $this->assertInstanceOf(Email::class, $all[0]);
        $this->assertTrue($all[0]->isPrimary());
        $this->assertEquals('primary@example.com', $all[0]->getEmail());
        $this->assertFalse($all[1]->isPrimary());
        $this->assertEquals('additional@example.com', $all[1]->getEmail());
    }

    public function testEmailCollectionIsEmpty(): void
    {
        $emptyCollection = new EmailCollection();
        $this->assertTrue($emptyCollection->isEmpty());

        $collection = new EmailCollection('test@example.com');
        $this->assertFalse($collection->isEmpty());
    }

    public function testEmailCollectionHasEmail(): void
    {
        $collection = new EmailCollection('primary@example.com', ['additional@example.com']);

        $this->assertTrue($collection->hasEmail('primary@example.com'));
        $this->assertTrue($collection->hasEmail('additional@example.com'));
        $this->assertFalse($collection->hasEmail('notfound@example.com'));
    }

    public function testEmailCollectionCount(): void
    {
        $collection = new EmailCollection('primary@example.com', ['a@example.com', 'b@example.com']);

        $this->assertEquals(3, $collection->count());

        $emptyCollection = new EmailCollection();
        $this->assertEquals(0, $emptyCollection->count());
    }

    public function testEmailCollectionAddAdditionalEmail(): void
    {
        $collection = new EmailCollection('primary@example.com');

        $collection->addAdditionalEmail('new@example.com');

        $this->assertEquals(['new@example.com'], $collection->getAdditionalEmails());
        $this->assertEquals(2, $collection->count());
    }

    public function testEmailCollectionAddAdditionalEmailNoDuplicates(): void
    {
        $collection = new EmailCollection('primary@example.com', ['existing@example.com']);

        $collection->addAdditionalEmail('existing@example.com');

        $this->assertEquals(['existing@example.com'], $collection->getAdditionalEmails());
        $this->assertEquals(2, $collection->count());
    }

    public function testEmailCollectionRemoveEmail(): void
    {
        $collection = new EmailCollection('primary@example.com', ['additional@example.com']);

        $collection->removeEmail('additional@example.com');

        $this->assertEquals([], $collection->getAdditionalEmails());
        $this->assertEquals(1, $collection->count());
    }

    public function testEmailCollectionRemovePrimaryEmail(): void
    {
        $collection = new EmailCollection('primary@example.com', ['additional@example.com']);

        $collection->removeEmail('primary@example.com');

        $this->assertNull($collection->getPrimaryEmail());
        $this->assertEquals(['additional@example.com'], $collection->getAdditionalEmails());
        $this->assertEquals(1, $collection->count());
    }

    public function testEmailCollectionSetters(): void
    {
        $collection = new EmailCollection();

        $collection->setPrimaryEmail('primary@example.com')
            ->setAdditionalEmails(['a@example.com', 'b@example.com']);

        $this->assertEquals('primary@example.com', $collection->getPrimaryEmail());
        $this->assertEquals(['a@example.com', 'b@example.com'], $collection->getAdditionalEmails());
    }
}
