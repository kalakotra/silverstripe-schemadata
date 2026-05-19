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
 *           type:        'ProfessionalService',
 *           id:          Director::absoluteBaseURL() . '#organization',
 *           name:        $config->Title,
 *           url:         Director::absoluteBaseURL(),
 *           logoUrl:     $config->hasMethod('getOrganizationLogoURL') ? (string) $config->getOrganizationLogoURL() : '',
 *           imageUrl:    Director::absoluteBaseURL() . 'assets/og-image.jpg',
 *           description: 'Profesionalna web agencija...',
 *           phone:       '+49 000 000000',
 *           email:       'info@example.com',
 *           address: [
 *               '@type' => 'PostalAddress',
 *               'streetAddress' => 'Ulica i broj 123',
 *               'addressLocality' => 'Bremen',
 *               'postalCode' => '28195',
 *               'addressCountry' => 'DE',
 *           ],
 *           geo: [
 *               '@type' => 'GeoCoordinates',
 *               'latitude' => 53.0793,
 *               'longitude' => 8.8017,
 *           ],
 *           openingHoursSpecification: [
 *               [
 *                   '@type' => 'OpeningHoursSpecification',
 *                   'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
 *                   'opens' => '09:00',
 *                   'closes' => '17:00',
 *               ],
 *           ],
 *           sameAs:      array_filter([$config->FacebookURL, $config->LinkedInURL]),
 *           areaServed: [
 *               ['@type' => 'Country', 'name' => 'Germany'],
 *               ['@type' => 'Country', 'name' => 'Bosnia and Herzegovina'],
 *           ],
 *           knowsAbout: [
 *               'Web Development',
 *               'Content Management Systems',
 *               'SilverStripe CMS',
 *           ],
 *       ))->getSchemaData();
 *   }
 */
final class OrganizationSchema implements SchemaProvider
{
    /**
    * @param array<int, string> $sameAs  Array of social/profile URLs
    * @param array<string, mixed> $address PostalAddress object as associative array
    * @param array<string, mixed> $geo GeoCoordinates object as associative array
    * @param array<int, array<string, mixed>> $openingHoursSpecification OpeningHoursSpecification objects
    * @param array<int, mixed> $areaServed Area objects/strings
    * @param array<int, string> $knowsAbout Topic names
     */
    public function __construct(
        private readonly string $name,
        private readonly string $url,
        private readonly array $type = ["Organization"],
        private readonly string $id = '',
        private readonly string $logoUrl = '',
        private readonly string $imageUrl = '',
        private readonly string $description = '',
        private readonly string $email = '',
        private readonly string $phone = '',
        private readonly array $address = [],
        private readonly array $geo = [],
        private readonly array $openingHoursSpecification = [],
        private readonly array $sameAs = [],
        private readonly array $areaServed = [],
        private readonly array $knowsAbout = [],
    ) {}

    public function getSchemaData(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => $this->type,
            'name'     => $this->name,
            'url'      => $this->url,
        ];

        if ($this->id !== '') {
            $data['@id'] = $this->id;
        }

        if ($this->logoUrl !== '') {
            $data['logo'] = $this->logoUrl;
        }

        if ($this->imageUrl !== '') {
            $data['image'] = $this->imageUrl;
        }

        if ($this->description !== '') {
            $data['description'] = $this->description;
        }

        if ($this->email !== '') {
            $data['email'] = $this->email;
        }

        if ($this->phone !== '') {
            $data['telephone'] = $this->phone;
        }

        if ($this->address !== []) {
            $data['address'] = $this->address;
        }

        if ($this->geo !== []) {
            $data['geo'] = $this->geo;
        }

        if ($this->openingHoursSpecification !== []) {
            $data['openingHoursSpecification'] = $this->openingHoursSpecification;
        }

        if ($this->sameAs !== []) {
            $sameAs = array_values(array_filter($this->sameAs));
            if ($sameAs !== []) {
                $data['sameAs'] = $sameAs;
            }
        }

        if ($this->areaServed !== []) {
            $data['areaServed'] = $this->areaServed;
        }

        if ($this->knowsAbout !== []) {
            $data['knowsAbout'] = $this->knowsAbout;
        }

        return $data;
    }
}
