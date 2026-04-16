<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData\Schema;

use Kalakotra\SchemaData\SchemaProvider;

/**
 * Schema.org/Organization
 *
 * Example usage on HomePage:
 *
 *   public function getSchemaData(): array
 *   {
 *       $config = SiteConfig::current_site_config();
 *       return (new OrganizationSchema(
 *           name:        $config->Title,
 *           url:         Director::absoluteBaseURL(),
 *           logoUrl:     $config->Logo()?->AbsoluteURL() ?? '',
 *           sameAs:      array_filter([$config->FacebookURL, $config->LinkedInURL]),
 *       ))->getSchemaData();
 *   }
 */
final class OrganizationSchema implements SchemaProvider
{
    /**
    * @param string[] $sameAs  Array of social/profile URLs
     */
    public function __construct(
        private readonly string $name,
        private readonly string $url,
        private readonly string $logoUrl  = '',
        private readonly string $email    = '',
        private readonly string $phone    = '',
        private readonly array  $sameAs   = [],
    ) {}

    public function getSchemaData(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => $this->name,
            'url'      => $this->url,
        ];

        if ($this->logoUrl !== '') {
            $data['logo'] = [
                '@type'      => 'ImageObject',
                'url'        => $this->logoUrl,
            ];
        }

        if ($this->email !== '') {
            $data['email'] = $this->email;
        }

        if ($this->phone !== '') {
            $data['telephone'] = $this->phone;
        }

        $filtered = array_values(array_filter($this->sameAs));
        if ($filtered !== []) {
            $data['sameAs'] = $filtered;
        }

        return $data;
    }
}
