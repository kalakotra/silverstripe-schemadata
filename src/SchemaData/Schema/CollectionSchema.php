<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData\Schema;

use Kalakotra\SchemaData\SchemaProvider;

/**
 * Schema.org/CollectionPage
 *
 * Use this for portfolio or project listing pages that contain
 * a collection of different creative or web deliverables.
 *
 * Example usage:
 *
 *   public function getSchemaData(): array
 *   {
 *       $organizationId = 'https://www.example.com/#organization';
 *
 *       return (new CollectionSchema(
 *           name: 'Our projects',
 *           url: $this->AbsoluteLink(),
 *           providerId: $organizationId,
 *           providerType: 'ProfessionalService',
 *           providerName: 'Agency Name',
 *           providerImage: 'https://www.example.com/assets/logo.png',
 *           hasPart: [
 *               [
 *                   'type' => 'WebSite',
 *                   'name' => 'Client Website',
 *                   'url' => 'https://client.com',
 *                   'creatorId' => $organizationId,
 *               ],
 *               [
 *                   'type' => 'CreativeWork',
 *                   'name' => 'Brand Identity Design',
 *                   'creatorId' => $organizationId,
 *               ],
 *               [
 *                   'type' => 'VisualArtwork',
 *                   'name' => 'Poster Campaign',
 *                   'artform' => 'Print',
 *                   'creatorId' => $organizationId,
 *               ],
 *           ],
 *       ))->getSchemaData();
 *   }
 */
final class CollectionSchema implements SchemaProvider
{
    /**
     * @param array<int, array{type: string, name: string, url?: string, artform?: string, creatorId?: string}> $hasPart
     */
    public function __construct(
        private readonly string $name,
        private readonly string $url,
        private readonly array  $provider = [],
        private readonly array $hasPart = [],
        private readonly string $type = 'CollectionPage',
    ) {}

    public function getSchemaData(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => $this->type,
            'name' => $this->name,
            'url' => $this->url,
        ];

        if ($this->provider !== []) {
            $data['provider'] = $this->provider;
        }

        $parts = $this->buildHasPart();
        if ($parts !== []) {
            $data['hasPart'] = $parts;
        }

        return $data;
    }

    /** @return array<int, array<string, mixed>> */
    private function buildHasPart(): array
    {
        $parts = [];

        foreach ($this->hasPart as $part) {
            if (empty($part['type']) || empty($part['name'])) {
                continue;
            }

            $entry = [
                '@type' => $part['type'],
                'name' => $part['name'],
            ];

            if (!empty($part['url'])) {
                $entry['url'] = $part['url'];
            }

            if (!empty($part['artform'])) {
                $entry['artform'] = $part['artform'];
            }

            if (!empty($part['creatorId'])) {
                $entry['creator'] = [
                    '@id' => $part['creatorId'],
                ];
            }

            $parts[] = $entry;
        }

        return $parts;
    }
}