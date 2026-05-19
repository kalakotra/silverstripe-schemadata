<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData\Schema;

use Kalakotra\SchemaData\SchemaProvider;

/**
 * Schema.org/CreativeWork + ItemPage
 *
 * Use this on individual project/portfolio item pages that need to be marked
 * as both creative work and item pages with creator and publisher information.
 *
 * Example usage on ProjectPage:
 *
 *   public function getSchemaData(): array
 *   {
 *       $organizationId = 'https://www.example.com/#organization';
 *
 *       return (new ProjectPageSchema(
 *           name: $this->Title,
 *           url: $this->AbsoluteLink(),
 *           description: $this->MetaDescription,
 *           imageUrl: $this->Image()?->AbsoluteURL() ?? '',
 *           creatorId: $organizationId,
 *           creatorName: 'Dubbel Späth GmbH & Co. KG',
 *           creatorType: 'ProfessionalService',
 *           publisherId: $organizationId,
 *           publisherName: 'Dubbel Späth GmbH & Co. KG',
 *           publisherType: 'ProfessionalService',
 *       ))->getSchemaData();
 *   }
 */
final class ProjectPageSchema implements SchemaProvider
{
    public function __construct(
        private readonly string $name,
        private readonly string $url,
        private readonly array $type = ["ItemPage"],
        private readonly string $description = '',
        private readonly string $imageUrl = '',
        private readonly array $creator = [],
        private readonly array $publisher = [],
    ) {}

    public function getSchemaData(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => $this->type,
            'name' => $this->name,
            'url' => $this->url,
        ];

        if ($this->description !== '') {
            $data['description'] = $this->description;
        }

        if ($this->imageUrl !== '') {
            $data['image'] = $this->imageUrl;
        }

        if (!empty($this->creator)) {
            $data['creator'] = $this->creator;
        }

        if (!empty($this->publisher)) {
            $data['publisher'] = $this->publisher;
        }

        return $data;
    }

    /** @return array<string, string> */
    private function buildEntity(string $id, string $name, string $type): array
    {
        $entity = [
            '@type' => $type,
        ];

        if ($id !== '') {
            $entity['@id'] = $id;
        }

        if ($name !== '') {
            $entity['name'] = $name;
        }

        return $entity;
    }
}
