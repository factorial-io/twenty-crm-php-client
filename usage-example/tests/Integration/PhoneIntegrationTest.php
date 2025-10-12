<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\DTO\EmailCollection;
use Factorial\TwentyCrm\DTO\Name;
use Factorial\TwentyCrm\DTO\Phone;
use Factorial\TwentyCrm\DTO\PhoneCollection;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

class PhoneIntegrationTest extends IntegrationTestCase
{
    public function testCreatePersonWithPhone(): void
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

        $person = $this->getPersonService()->createInstance();
        $person->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person->setName(new Name(
            firstName: $this->generateTestName('PhoneTest'),
            lastName: 'Test'
        ));
        $person->setPhones($phoneCollection);
        $person->setJobTitle('Phone Tester');

        $created = $this->getPersonService()->create($person);
        $this->trackResource('person', $created->getId());

        // Verify phone was created
        $this->assertNotNull($created->getPhones());
        $this->assertFalse($created->getPhones()->isEmpty());

        $primaryPhone = $created->getPhones()->getPrimaryPhone();
        $this->assertNotNull($primaryPhone);
        $this->assertStringContainsString('1234567890', $primaryPhone->getNumber());
    }

    public function testCreatePersonWithSimplePhone(): void
    {
        $this->requireClient();

        $person = $this->getPersonService()->createInstance();
        $person->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person->setName(new Name(
            firstName: $this->generateTestName('SimplePhone'),
            lastName: 'Test'
        ));
        $person->setJobTitle('Simple Phone Tester');

        // Set phone using PhoneCollection
        $person->setPhones(new PhoneCollection(
            primaryPhone: new Phone(
                number: '9876543210',
                countryCode: 'US',
                callingCode: '+1'
            )
        ));

        $created = $this->getPersonService()->create($person);
        $this->trackResource('person', $created->getId());

        // Verify phone was created
        $this->assertNotNull($created->getPhones());
        $primaryPhone = $created->getPhones()->getPrimaryPhone();
        $this->assertNotNull($primaryPhone);
        $this->assertStringContainsString('9876543210', $primaryPhone->getNumber());
    }

    public function testReadPersonWithPhone(): void
    {
        $this->requireClient();

        // Create person with phone
        $phoneCollection = new PhoneCollection(
            primaryPhone: new Phone(
                number: '5551234567',
                countryCode: 'US',
                callingCode: '+1'
            )
        );

        $person = $this->getPersonService()->createInstance();
        $person->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person->setName(new Name(
            firstName: $this->generateTestName('ReadPhone'),
            lastName: 'Test'
        ));
        $person->setPhones($phoneCollection);

        $created = $this->getPersonService()->create($person);
        $this->trackResource('person', $created->getId());

        // Read it back
        $retrieved = $this->getPersonService()->getById($created->getId());

        $this->assertNotNull($retrieved);
        $this->assertNotNull($retrieved->getPhones());

        $retrievedPhone = $retrieved->getPhones()->getPrimaryPhone();
        $this->assertNotNull($retrievedPhone);
        $this->assertStringContainsString('5551234567', $retrievedPhone->getNumber());
    }

    public function testUpdatePersonPhone(): void
    {
        $this->requireClient();

        // Create person with initial phone
        $person = $this->getPersonService()->createInstance();
        $person->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person->setName(new Name(
            firstName: $this->generateTestName('UpdatePhone'),
            lastName: 'Test'
        ));
        $person->setPhones(new PhoneCollection(
            primaryPhone: new Phone(
                number: '1111111111',
                countryCode: 'US',
                callingCode: '+1'
            )
        ));

        $created = $this->getPersonService()->create($person);
        $this->trackResource('person', $created->getId());

        // Verify initial phone
        $this->assertNotNull($created->getPhones());
        $this->assertStringContainsString('1111111111', $created->getPhones()->getPrimaryPhone()->getNumber());

        // Update phone
        $created->setPhones(new PhoneCollection(
            primaryPhone: new Phone(
                number: '2222222222',
                countryCode: 'US',
                callingCode: '+1'
            )
        ));
        $updated = $this->getPersonService()->update($created);

        // Verify updated phone
        $this->assertNotNull($updated->getPhones());
        $this->assertStringContainsString('2222222222', $updated->getPhones()->getPrimaryPhone()->getNumber());
        $this->assertEquals($created->getId(), $updated->getId());
    }
}
