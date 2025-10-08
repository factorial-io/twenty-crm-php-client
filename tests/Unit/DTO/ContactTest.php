<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Unit\DTO;

use Factorial\TwentyCrm\DTO\Contact;
use Factorial\TwentyCrm\DTO\Link;
use Factorial\TwentyCrm\DTO\LinkCollection;
use Factorial\TwentyCrm\DTO\Phone;
use Factorial\TwentyCrm\DTO\PhoneCollection;
use Factorial\TwentyCrm\Tests\TestCase;

class ContactTest extends TestCase
{
    public function testCreateContactWithBasicData(): void
    {
        $contact = new Contact(
            id: 'test-123',
            email: 'test@example.com',
            firstName: 'John',
            lastName: 'Doe'
        );

        $this->assertEquals('test-123', $contact->getId());
        $this->assertEquals('test@example.com', $contact->getEmail());
        $this->assertEquals('John', $contact->getFirstName());
        $this->assertEquals('Doe', $contact->getLastName());
        $this->assertEquals('John Doe', $contact->getFullName());
    }

    public function testCreateContactFromArray(): void
    {
        $data = [
            'id' => 'test-456',
            'name' => [
                'firstName' => 'Jane',
                'lastName' => 'Smith',
            ],
            'emails' => [
                'primaryEmail' => 'jane@example.com',
            ],
            'phones' => [
                'primaryPhoneNumber' => '+1234567890',
            ],
            'jobTitle' => 'Developer',
            'companyId' => 'company-123',
            'createdAt' => '2024-01-01T00:00:00Z',
        ];

        $contact = Contact::fromArray($data);

        $this->assertEquals('test-456', $contact->getId());
        $this->assertEquals('Jane', $contact->getFirstName());
        $this->assertEquals('Smith', $contact->getLastName());
        $this->assertEquals('jane@example.com', $contact->getEmail());
        $this->assertEquals('+1234567890', $contact->getPhone());
        $this->assertEquals('Developer', $contact->getJobTitle());
        $this->assertEquals('company-123', $contact->getCompanyId());
        $this->assertNotNull($contact->getCreatedAt());
    }

    public function testContactToArray(): void
    {
        $contact = new Contact(
            email: 'test@example.com',
            firstName: 'John',
            lastName: 'Doe',
            jobTitle: 'Manager'
        );
        $contact->setPhone('+9876543210');

        $array = $contact->toArray();

        $this->assertEquals('test@example.com', $array['emails']['primaryEmail']);
        $this->assertEquals('John', $array['name']['firstName']);
        $this->assertEquals('Doe', $array['name']['lastName']);
        $this->assertEquals('+9876543210', $array['phones']['primaryPhoneNumber']);
        $this->assertEquals('Manager', $array['jobTitle']);
    }

    public function testSetters(): void
    {
        $contact = new Contact();

        $contact->setEmail('new@example.com')
            ->setFirstName('Updated')
            ->setLastName('Name')
            ->setPhone('+1111111111')
            ->setJobTitle('Engineer')
            ->setCompanyId('company-456');

        $this->assertEquals('new@example.com', $contact->getEmail());
        $this->assertEquals('Updated', $contact->getFirstName());
        $this->assertEquals('Name', $contact->getLastName());
        $this->assertEquals('+1111111111', $contact->getPhone());
        $this->assertEquals('Engineer', $contact->getJobTitle());
        $this->assertEquals('company-456', $contact->getCompanyId());
    }

    public function testCustomFields(): void
    {
        $contact = new Contact(
            firstName: 'John',
            lastName: 'Doe',
            customFields: ['customField1' => 'value1']
        );

        $this->assertEquals('value1', $contact->getCustomField('customField1'));
        $this->assertNull($contact->getCustomField('nonExistent'));

        $contact->setCustomField('customField2', 'value2');
        $this->assertEquals('value2', $contact->getCustomField('customField2'));

        $allCustomFields = $contact->getCustomFields();
        $this->assertCount(2, $allCustomFields);
        $this->assertEquals('value1', $allCustomFields['customField1']);
        $this->assertEquals('value2', $allCustomFields['customField2']);
    }

    public function testFullNameWithMissingParts(): void
    {
        $contact1 = new Contact(firstName: 'John');
        $this->assertEquals('John', $contact1->getFullName());

        $contact2 = new Contact(lastName: 'Doe');
        $this->assertEquals('Doe', $contact2->getFullName());

        $contact3 = new Contact();
        $this->assertEquals('', $contact3->getFullName());
    }

    public function testFromArrayWithAdditionalEmails(): void
    {
        $data = [
            'name' => [
                'firstName' => 'Test',
                'lastName' => 'User',
            ],
            'emails' => [
                'additionalEmails' => ['fallback@example.com'],
            ],
        ];

        $contact = Contact::fromArray($data);
        $this->assertEquals('fallback@example.com', $contact->getEmail());
    }

    public function testFromArrayWithAdditionalPhones(): void
    {
        $data = [
            'name' => [
                'firstName' => 'Test',
                'lastName' => 'User',
            ],
            'phones' => [
                'additionalPhones' => ['+9999999999'],
            ],
        ];

        $contact = Contact::fromArray($data);
        $this->assertEquals('+9999999999', $contact->getPhone());
    }

    public function testToArrayExcludesNullValues(): void
    {
        $contact = new Contact(
            firstName: 'John',
            lastName: 'Doe'
        );

        $array = $contact->toArray();

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('emails', $array);
        $this->assertArrayNotHasKey('phones', $array);
        $this->assertArrayNotHasKey('jobTitle', $array);
    }

    public function testToArrayIncludesIdWhenPresent(): void
    {
        $contact = new Contact(
            id: 'test-789',
            firstName: 'John',
            lastName: 'Doe'
        );

        $array = $contact->toArray();
        $this->assertEquals('test-789', $array['id']);
    }

    public function testMobilePhones(): void
    {
        $mobilePhones = new PhoneCollection(
            primaryPhone: new Phone('+15551234567', 'US', '+1')
        );

        $contact = new Contact(
            firstName: 'John',
            lastName: 'Doe',
            mobilePhones: $mobilePhones
        );

        $this->assertNotNull($contact->getMobilePhones());
        $this->assertEquals('+15551234567', $contact->getMobilePhone());

        // Test setter
        $contact->setMobilePhone('+15559876543');
        $this->assertStringContainsString('5559876543', $contact->getMobilePhone());
    }

    public function testLinkedInLink(): void
    {
        $linkedInLink = new LinkCollection(
            primaryLink: new Link('https://linkedin.com/in/johndoe', 'John Doe')
        );

        $contact = new Contact(
            firstName: 'John',
            lastName: 'Doe',
            linkedInLink: $linkedInLink
        );

        $this->assertNotNull($contact->getLinkedInLink());
        $this->assertEquals('https://linkedin.com/in/johndoe', $contact->getLinkedInUrl());

        // Test setter
        $contact->setLinkedInUrl('https://linkedin.com/in/janedoe', 'Jane Doe');
        $this->assertEquals('https://linkedin.com/in/janedoe', $contact->getLinkedInUrl());
    }

    public function testXLink(): void
    {
        $xLink = new LinkCollection(
            primaryLink: new Link('https://x.com/johndoe', '@johndoe')
        );

        $contact = new Contact(
            firstName: 'John',
            lastName: 'Doe',
            xLink: $xLink
        );

        $this->assertNotNull($contact->getXLink());
        $this->assertEquals('https://x.com/johndoe', $contact->getXUrl());

        // Test setter
        $contact->setXUrl('https://x.com/janedoe', '@janedoe');
        $this->assertEquals('https://x.com/janedoe', $contact->getXUrl());
    }

    public function testFromArrayWithMobilePhonesAndLinks(): void
    {
        $data = [
            'name' => [
                'firstName' => 'Test',
                'lastName' => 'User',
            ],
            'emails' => [
                'primaryEmail' => 'test@example.com',
            ],
            'mobilePhones' => [
                'primaryPhoneNumber' => '5551234567',
                'primaryPhoneCountryCode' => 'US',
                'primaryPhoneCallingCode' => '+1',
            ],
            'linkedinLink' => [
                'primaryLinkUrl' => 'https://linkedin.com/in/testuser',
                'primaryLinkLabel' => 'Test User',
            ],
            'xLink' => [
                'primaryLinkUrl' => 'https://x.com/testuser',
                'primaryLinkLabel' => '@testuser',
            ],
        ];

        $contact = Contact::fromArray($data);

        $this->assertNotNull($contact->getMobilePhones());
        $this->assertStringContainsString('5551234567', $contact->getMobilePhone());

        $this->assertNotNull($contact->getLinkedInLink());
        $this->assertEquals('https://linkedin.com/in/testuser', $contact->getLinkedInUrl());

        $this->assertNotNull($contact->getXLink());
        $this->assertEquals('https://x.com/testuser', $contact->getXUrl());
    }

    public function testToArrayWithMobilePhonesAndLinks(): void
    {
        $contact = new Contact(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com'
        );

        $contact->setMobilePhone('+15551234567');
        $contact->setLinkedInUrl('https://linkedin.com/in/johndoe', 'John Doe');
        $contact->setXUrl('https://x.com/johndoe', '@johndoe');

        $array = $contact->toArray();

        $this->assertArrayHasKey('mobilePhones', $array);
        $this->assertStringContainsString('555', $array['mobilePhones']['primaryPhoneNumber']);

        $this->assertArrayHasKey('linkedinLink', $array);
        $this->assertEquals('https://linkedin.com/in/johndoe', $array['linkedinLink']['primaryLinkUrl']);

        $this->assertArrayHasKey('xLink', $array);
        $this->assertEquals('https://x.com/johndoe', $array['xLink']['primaryLinkUrl']);
    }
}
