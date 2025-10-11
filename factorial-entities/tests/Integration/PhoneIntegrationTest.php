<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\DTO\Contact;
use Factorial\TwentyCrm\DTO\Phone;
use Factorial\TwentyCrm\DTO\PhoneCollection;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

class PhoneIntegrationTest extends IntegrationTestCase
{
    public function testCreateContactWithPhone(): void
    {
        $this->requireClient();

        // Create a phone collection with primary phone
        $phoneCollection = new PhoneCollection(
            primaryPhone: new Phone(
                number: '1234567890',
                countryCode: 'US',
                callingCode: '+1'
            )
        );

        $contact = new Contact(
            email: $this->generateTestEmail(),
            firstName: $this->generateTestName('PhoneTest'),
            lastName: 'Test',
            phones: $phoneCollection,
            jobTitle: 'Phone Tester'
        );

        $created = $this->client->contacts()->create($contact);
        $this->trackResource('contact', $created->getId());

        // Verify phone was created
        $this->assertNotNull($created->getPhones());
        $this->assertFalse($created->getPhones()->isEmpty());

        $primaryPhone = $created->getPhones()->getPrimaryPhone();
        $this->assertNotNull($primaryPhone);
        $this->assertEquals('1234567890', $primaryPhone->getNumber());
    }

    public function testCreateContactWithSimplePhone(): void
    {
        $this->requireClient();

        $contact = new Contact(
            email: $this->generateTestEmail(),
            firstName: $this->generateTestName('SimplePhone'),
            lastName: 'Test',
            jobTitle: 'Simple Phone Tester'
        );

        // Use simple setPhone method (backward compatibility)
        $contact->setPhone('+19876543210');

        $created = $this->client->contacts()->create($contact);
        $this->trackResource('contact', $created->getId());

        // Verify phone was created (API may strip the + prefix)
        $this->assertNotNull($created->getPhone());
        $this->assertStringContainsString('9876543210', $created->getPhone());
    }

    public function testReadContactWithPhone(): void
    {
        $this->requireClient();

        // Create contact with phone
        $phoneCollection = new PhoneCollection(
            primaryPhone: new Phone(
                number: '5551234567',
                countryCode: 'US',
                callingCode: '+1'
            )
        );

        $contact = new Contact(
            email: $this->generateTestEmail(),
            firstName: $this->generateTestName('ReadPhone'),
            lastName: 'Test',
            phones: $phoneCollection
        );

        $created = $this->client->contacts()->create($contact);
        $this->trackResource('contact', $created->getId());

        // Read it back
        $retrieved = $this->client->contacts()->getById($created->getId());

        $this->assertNotNull($retrieved);
        $this->assertNotNull($retrieved->getPhones());

        $retrievedPhone = $retrieved->getPhones()->getPrimaryPhone();
        $this->assertNotNull($retrievedPhone);
        $this->assertEquals('5551234567', $retrievedPhone->getNumber());
    }

    public function testUpdateContactPhone(): void
    {
        $this->requireClient();

        // Create contact with initial phone
        $contact = new Contact(
            email: $this->generateTestEmail(),
            firstName: $this->generateTestName('UpdatePhone'),
            lastName: 'Test'
        );
        $contact->setPhone('+11111111111');

        $created = $this->client->contacts()->create($contact);
        $this->trackResource('contact', $created->getId());

        // Verify initial phone (API may strip + prefix)
        $this->assertStringContainsString('1111111111', $created->getPhone());

        // Update phone
        $created->setPhone('+12222222222');
        $updated = $this->client->contacts()->update($created);

        // Verify updated phone
        $this->assertStringContainsString('2222222222', $updated->getPhone());
        $this->assertEquals($created->getId(), $updated->getId());
    }
}
