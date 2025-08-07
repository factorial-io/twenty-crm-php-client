<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Client;

use Factorial\TwentyCrm\Services\ContactServiceInterface;
use Factorial\TwentyCrm\Services\CompanyServiceInterface;

/**
 * Interface for Twenty CRM client.
 */
interface ClientInterface {

  /**
   * Get the contact service.
   *
   * @return \Factorial\TwentyCrm\Services\ContactServiceInterface
   *   The contact service instance.
   */
  public function contacts(): ContactServiceInterface;

  /**
   * Get the company service.
   *
   * @return \Factorial\TwentyCrm\Services\CompanyServiceInterface
   *   The company service instance.
   */
  public function companies(): CompanyServiceInterface;

}
