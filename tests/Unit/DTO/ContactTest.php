<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Unit\DTO;

use Factorial\TwentyCrm\DTO\Contact;
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
}
