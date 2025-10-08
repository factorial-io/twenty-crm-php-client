<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\DTO;

/**
 * Link data transfer object.
 */
class Link
{
    /**
     * Link constructor.
     *
     * @param string|null $url
     *   The URL.
     * @param string|null $label
     *   The label.
     */
    public function __construct(
        private ?string $url = null,
        private ?string $label = null,
    ) {
    }

    /**
     * Create Link from array data.
     *
     * @param array $data
     *   The link data.
     *
     * @return self
     *   The Link instance.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            url: $data['url'] ?? null,
            label: $data['label'] ?? null,
        );
    }

    /**
     * Convert Link to array.
     *
     * @return array
     *   The link data as array.
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->url !== null) {
            $data['url'] = $this->url;
        }

        if ($this->label !== null) {
            $data['label'] = $this->label;
        }

        return $data;
    }

    // Getters

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    // Setters

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
