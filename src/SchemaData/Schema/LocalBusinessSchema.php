<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData\Schema;

use Kalakotra\SchemaData\SchemaProvider;

/**
 * Schema.org/LocalBusiness
 *
 * Example usage:
 *
 *   public function getSchemaData(): array
 *   {
 *       $config = SiteConfig::current_site_config();
 *       return (new LocalBusinessSchema(
 *           name:      $config->Title,
 *           street:    $config->Street,
 *           city:      $config->City,
 *           country:   'BA',
 *           phone:     $config->Phone,
 *           latitude:  (float) $config->Latitude,
 *           longitude: (float) $config->Longitude,
 *       ))->getSchemaData();
 *   }
 */
final class LocalBusinessSchema implements SchemaProvider
{
    public function __construct(
        private readonly string $name,
        private readonly string $street,
        private readonly string $city,
        private readonly string $country      = 'BA',
        private readonly string $postalCode   = '',
        private readonly string $phone        = '',
        private readonly string $email        = '',
        private readonly string $url          = '',
        private readonly float  $latitude     = 0.0,
        private readonly float  $longitude    = 0.0,
        private readonly string $openingHours = '', // e.g. "Mo-Fr 09:00-17:00"
    ) {}

    public function getSchemaData(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'LocalBusiness',
            'name'     => $this->name,
            'address'  => array_filter([
                '@type'           => 'PostalAddress',
                'streetAddress'   => $this->street,
                'addressLocality' => $this->city,
                'postalCode'      => $this->postalCode,
                'addressCountry'  => $this->country,
            ]),
        ];

        if ($this->latitude !== 0.0 && $this->longitude !== 0.0) {
            $data['geo'] = [
                '@type'     => 'GeoCoordinates',
                'latitude'  => $this->latitude,
                'longitude' => $this->longitude,
            ];
        }

        if ($this->phone !== '') {
            $data['telephone'] = $this->phone;
        }

        if ($this->email !== '') {
            $data['email'] = $this->email;
        }

        if ($this->url !== '') {
            $data['url'] = $this->url;
        }

        if ($this->openingHours !== '') {
            $data['openingHours'] = $this->openingHours;
        }

        return $data;
    }
}
