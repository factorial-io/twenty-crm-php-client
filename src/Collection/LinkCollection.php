<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Collection;

use Factorial\TwentyCrm\DTO\Link;

/**
 * Link collection data transfer object.
 */
class LinkCollection
{
    /**
     * LinkCollection constructor.
     *
     * @param \Factorial\TwentyCrm\DTO\Link|null $primaryLink
     *   The primary link.
     * @param array $secondaryLinks
     *   Array of secondary links.
     */
    public function __construct(
        private ?Link $primaryLink = null,
        private array $secondaryLinks = [],
    ) {
    }

    /**
     * Create LinkCollection from array data.
     *
     * @param array $data
     *   The link collection data.
     *
     * @return self
     *   The LinkCollection instance.
     */
    public static function fromArray(array $data): self
    {
        // Create primary link
        $primaryLink = null;
        if (isset($data['primaryLinkUrl']) || isset($data['primaryLinkLabel'])) {
            $primaryLink = Link::fromArray([
              'url' => $data['primaryLinkUrl'] ?? null,
              'label' => $data['primaryLinkLabel'] ?? null,
            ]);
        }

        // Create secondary links
        $secondaryLinks = [];
        if (isset($data['secondaryLinks']) && is_array($data['secondaryLinks'])) {
            foreach ($data['secondaryLinks'] as $linkData) {
                if (is_array($linkData)) {
                    $secondaryLinks[] = Link::fromArray($linkData);
                }
            }
        }

        return new self($primaryLink, $secondaryLinks);
    }

    /**
     * Convert LinkCollection to array.
     *
     * @return array
     *   The link collection data as array.
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->primaryLink !== null) {
            $data['primaryLinkUrl'] = $this->primaryLink->getUrl();
            $data['primaryLinkLabel'] = $this->primaryLink->getLabel();
        }

        if (!empty($this->secondaryLinks)) {
            $data['secondaryLinks'] = array_map(fn ($link) => $link->toArray(), $this->secondaryLinks);
        }

        return $data;
    }

    // Getters

    public function getPrimaryLink(): ?Link
    {
        return $this->primaryLink;
    }

    public function getSecondaryLinks(): array
    {
        return $this->secondaryLinks;
    }

    public function getAllLinks(): array
    {
        $links = [];
        if ($this->primaryLink !== null) {
            $links[] = $this->primaryLink;
        }

        return array_merge($links, $this->secondaryLinks);
    }

    public function isEmpty(): bool
    {
        return $this->primaryLink === null && empty($this->secondaryLinks);
    }

    // Setters

    public function setPrimaryLink(?Link $primaryLink): self
    {
        $this->primaryLink = $primaryLink;

        return $this;
    }

    public function setSecondaryLinks(array $secondaryLinks): self
    {
        $this->secondaryLinks = $secondaryLinks;

        return $this;
    }

    public function addSecondaryLink(Link $link): self
    {
        $this->secondaryLinks[] = $link;

        return $this;
    }
}
