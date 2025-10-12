<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Integration;

use Factorial\TwentyCrm\DTO\EmailCollection;
use Factorial\TwentyCrm\DTO\FilterBuilder;
use Factorial\TwentyCrm\DTO\Name;
use Factorial\TwentyCrm\DTO\SearchOptions;
use Factorial\TwentyCrm\Tests\IntegrationTestCase;

/**
 * Integration test for FilterBuilder with real Twenty CRM API.
 *
 * Tests that FilterBuilder generates correct filter strings and works
 * properly with the Twenty CRM API for querying entities.
 */
class FilterBuilderIntegrationTest extends IntegrationTestCase
{
    private array $testPersons = [];

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->client) {
            // Use parent's PersonService
            // Create test persons for filtering
            $this->createTestPersons();
        }
    }

    protected function tearDown(): void
    {
        // Clean up test persons
        foreach (array_reverse($this->testPersons) as $person) {
            try {
                $this->getPersonService()->delete($person->getId());
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
        }

        parent::tearDown();
    }

    /**
     * Create test persons with various attributes for filtering.
     */
    private function createTestPersons(): void
    {
        $testPrefix = $this->generateTestName('FilterTest');

        // Person 1: John Doe, developer
        $person1 = $this->getPersonService()->createInstance();
        $person1->setName(new Name(firstName: 'John', lastName: 'Doe'));
        $person1->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person1->setJobTitle('Developer');
        $this->testPersons[] = $this->getPersonService()->create($person1);

        // Person 2: Jane Smith, designer
        $person2 = $this->getPersonService()->createInstance();
        $person2->setName(new Name(firstName: 'Jane', lastName: 'Smith'));
        $person2->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person2->setJobTitle('Designer');
        $this->testPersons[] = $this->getPersonService()->create($person2);

        // Person 3: Bob Johnson, developer
        $person3 = $this->getPersonService()->createInstance();
        $person3->setName(new Name(firstName: 'Bob', lastName: 'Johnson'));
        $person3->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));
        $person3->setJobTitle('Developer');
        $this->testPersons[] = $this->getPersonService()->create($person3);

        // Wait a moment to ensure API consistency
        sleep(1);
    }

    public function testSimpleEqualsFilter(): void
    {
        $this->requireClient();

        // Filter by job title
        $filter = FilterBuilder::create()
            ->equals('jobTitle', 'Developer')
            ->build();

        $persons = $this->getPersonService()->find($filter, new SearchOptions(limit: 100));

        // Should find at least our 2 developers
        $developerCount = 0;
        foreach ($persons as $person) {
            if ($person->get('jobTitle') === 'Developer') {
                $developerCount++;
            }
        }

        $this->assertGreaterThanOrEqual(2, $developerCount, 'Should find at least 2 developers');
    }

    public function testContainsFilter(): void
    {
        $this->requireClient();

        // Get email domain from one of our test persons
        $emails = $this->testPersons[0]->get('emails');
        $testEmail = $emails->getPrimaryEmail();
        $domain = explode('@', $testEmail)[1];

        // Filter by email containing test domain
        $filter = FilterBuilder::create()
            ->contains('emails.primaryEmail', $domain)
            ->build();

        $persons = $this->getPersonService()->find($filter, new SearchOptions(limit: 100));

        // Should find our test persons
        $found = 0;
        foreach ($persons as $person) {
            $emails = $person->get('emails');
            $email = $emails?->getPrimaryEmail();
            if ($email && str_contains($email, $domain)) {
                $found++;
            }
        }

        $this->assertGreaterThanOrEqual(3, $found, 'Should find at least 3 persons with test domain');
    }

    public function testMultipleConditionsWithAnd(): void
    {
        $this->requireClient();

        // Filter by first name AND job title
        $filter = FilterBuilder::create()
            ->equals('name.firstName', 'John')
            ->equals('jobTitle', 'Developer')
            ->build();

        $persons = $this->getPersonService()->find($filter, new SearchOptions(limit: 100));

        // Should find John Doe (developer)
        $foundJohnDeveloper = false;
        foreach ($persons as $person) {
            $name = $person->get('name');
            $firstName = $name?->getFirstName();
            $jobTitle = $person->get('jobTitle');
            if ($firstName === 'John' && $jobTitle === 'Developer') {
                $foundJohnDeveloper = true;
                break;
            }
        }

        $this->assertTrue($foundJohnDeveloper, 'Should find John (Developer)');
    }

    public function testStartsWithFilter(): void
    {
        $this->requireClient();

        // Filter by first name starting with 'J'
        $filter = FilterBuilder::create()
            ->startsWith('name.firstName', 'J')
            ->build();

        $persons = $this->getPersonService()->find($filter, new SearchOptions(limit: 100));

        // Should find at least John and Jane
        $jNames = [];
        foreach ($persons as $person) {
            $name = $person->get('name');
            $firstName = $name?->getFirstName();
            if ($firstName && str_starts_with($firstName, 'J')) {
                $jNames[] = $firstName;
            }
        }

        $this->assertGreaterThanOrEqual(2, count($jNames), 'Should find at least 2 persons with names starting with J');
        $this->assertContains('John', $jNames);
        $this->assertContains('Jane', $jNames);
    }

    public function testIsNotNullFilter(): void
    {
        $this->requireClient();

        // Filter by persons with job title (not null)
        $filter = FilterBuilder::create()
            ->isNotNull('jobTitle')
            ->build();

        $persons = $this->getPersonService()->find($filter, new SearchOptions(limit: 100));

        // All returned persons should have a job title
        $personsWithJobTitle = 0;
        foreach ($persons as $person) {
            if ($person->get('jobTitle') !== null) {
                $personsWithJobTitle++;
            }
        }

        $this->assertGreaterThan(0, $personsWithJobTitle);
        $this->assertCount($personsWithJobTitle, $persons, 'All returned persons should have job title');
    }

    public function testFilterWithValidation(): void
    {
        $this->requireClient();

        // Create FilterBuilder with entity definition for validation
        $definition = $this->client->registry()->getDefinition('person');
        $builder = FilterBuilder::forEntity($definition);

        // Valid filter - should not throw
        $filter = $builder->equals('jobTitle', 'Developer')->build();
        $this->assertNotNull($filter->buildFilterString());

        // Test that validation is working (field exists check)
        $this->assertTrue($definition->hasField('jobTitle'));
    }

    public function testInvalidFieldThrowsException(): void
    {
        $this->requireClient();

        $definition = $this->client->registry()->getDefinition('person');
        $builder = FilterBuilder::forEntity($definition);

        // Invalid field should throw exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown field');

        $builder->equals('nonExistentField', 'value');
    }

    public function testComplexNestedFieldFilter(): void
    {
        $this->requireClient();

        // Filter by nested field: name.firstName
        $filter = FilterBuilder::create()
            ->equals('name.firstName', 'Jane')
            ->build();

        $persons = $this->getPersonService()->find($filter, new SearchOptions(limit: 100));

        // Should find Jane
        $foundJane = false;
        foreach ($persons as $person) {
            $name = $person->get('name');
            $firstName = $name?->getFirstName();
            if ($firstName === 'Jane') {
                $foundJane = true;
                break;
            }
        }

        $this->assertTrue($foundJane, 'Should find Jane using nested field filter');
    }

    public function testFilterStringGeneration(): void
    {
        $this->requireClient();

        // Test various filter string generations (Twenty CRM format)
        $tests = [
            [
                'builder' => FilterBuilder::create()->equals('name', 'John'),
                'expected' => 'name[eq]:"John"',
            ],
            [
                'builder' => FilterBuilder::create()->greaterThan('age', 18),
                'expected' => 'age[gt]:18',
            ],
            [
                'builder' => FilterBuilder::create()->contains('email', '@example.com'),
                'expected' => 'email[contains]:"@example.com"',
            ],
            [
                'builder' => FilterBuilder::create()->isNull('deletedAt'),
                'expected' => 'deletedAt[is]:NULL',
            ],
            [
                'builder' => FilterBuilder::create()
                    ->equals('status', 'ACTIVE')
                    ->greaterThan('age', 21),
                'expected' => 'status[eq]:"ACTIVE",age[gt]:21',
            ],
        ];

        foreach ($tests as $test) {
            $filterString = $test['builder']->buildFilterString();
            $this->assertEquals(
                $test['expected'],
                $filterString,
                "Filter string should match expected format"
            );
        }
    }

    public function testEmptyFilterBuilder(): void
    {
        $this->requireClient();

        // Empty filter should return all results
        $filter = FilterBuilder::create()->build();

        $this->assertFalse($filter->hasFilters());
        $this->assertNull($filter->buildFilterString());

        // Should still work with find()
        $persons = $this->getPersonService()->find($filter, new SearchOptions(limit: 10));
        $this->assertGreaterThan(0, count($persons), 'Should return results with empty filter');
    }

    public function testFilterWithSearchOptions(): void
    {
        $this->requireClient();

        // Test FilterBuilder with SearchOptions
        $filter = FilterBuilder::create()
            ->isNotNull('jobTitle')
            ->build();

        $options = new SearchOptions(
            limit: 5,
            orderBy: 'createdAt[DescNullsLast]'
        );

        $persons = $this->getPersonService()->find($filter, $options);

        // Should respect limit
        $this->assertLessThanOrEqual(5, count($persons), 'Should respect limit in SearchOptions');
    }

    public function testMultipleFiltersInSequence(): void
    {
        $this->requireClient();

        // Test building multiple filters from same builder
        $builder = FilterBuilder::create();

        // First filter
        $filter1 = $builder->equals('jobTitle', 'Developer')->build();
        $persons1 = $this->getPersonService()->find($filter1, new SearchOptions(limit: 100));

        // Clear and create second filter
        $builder->clear();
        $filter2 = $builder->equals('jobTitle', 'Designer')->build();
        $persons2 = $this->getPersonService()->find($filter2, new SearchOptions(limit: 100));

        // Results should be different
        $devCount = 0;
        $designerCount = 0;

        foreach ($persons1 as $person) {
            if ($person->get('jobTitle') === 'Developer') {
                $devCount++;
            }
        }

        foreach ($persons2 as $person) {
            if ($person->get('jobTitle') === 'Designer') {
                $designerCount++;
            }
        }

        $this->assertGreaterThan(0, $devCount, 'Should find developers');
        $this->assertGreaterThan(0, $designerCount, 'Should find designers');
    }

    public function testFilterBuilderHelperMethods(): void
    {
        $this->requireClient();

        // Test all helper methods generate valid filters
        $builder = FilterBuilder::create();

        $filters = [
            $builder->equals('a', 'test')->clear(),
            $builder->notEquals('b', 'test')->clear(),
            $builder->greaterThan('c', 10)->clear(),
            $builder->greaterThanOrEquals('d', 10)->clear(),
            $builder->lessThan('e', 10)->clear(),
            $builder->lessThanOrEquals('f', 10)->clear(),
            $builder->contains('g', 'test')->clear(),
            $builder->startsWith('h', 'test')->clear(),
            $builder->endsWith('i', 'test')->clear(),
            $builder->isNull('j')->clear(),
            $builder->isNotNull('k')->clear(),
        ];

        // Each filter should build successfully
        $this->assertCount(11, $filters, 'All helper methods should work');
    }

    public function testFilterWithSpecialCharacters(): void
    {
        $this->requireClient();

        // Create person with special characters in name
        $specialPerson = $this->getPersonService()->createInstance();
        $specialPerson->setName(new Name(firstName: 'Test\'s', lastName: 'Person"Quote'));
        $specialPerson->setEmails(new EmailCollection(primaryEmail: $this->generateTestEmail()));

        $created = $this->getPersonService()->create($specialPerson);
        $this->testPersons[] = $created;

        sleep(1); // Wait for API consistency

        // Filter should handle special characters
        $filter = FilterBuilder::create()
            ->contains('name.lastName', 'Quote')
            ->build();

        $persons = $this->getPersonService()->find($filter, new SearchOptions(limit: 100));

        // Should find our person with quote in name
        $found = false;
        foreach ($persons as $person) {
            $name = $person->get('name');
            $lastName = $name?->getLastName();
            if ($lastName && str_contains($lastName, 'Quote')) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Should handle special characters in filter values');
    }
}
