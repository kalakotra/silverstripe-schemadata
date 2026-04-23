<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData\Schema;

use Kalakotra\SchemaData\SchemaProvider;

/**
 * Schema.org/EducationEvent for a single event date.
 *
 * Example usage on EventPage (single date):
 *
 *   public function getSchemaData(): array
 *   {
 *       $date    = $this->EventDate();
 *       $event = $date->Event();
 *
 *       return (new EventSchema(
 *           title:          $event->Title,
 *           description:    $event->Summary,
 *           startDate:      $date->dbObject('StartDate')->Rfc3339(),
 *           endDate:        $date->dbObject('EndDate')->Rfc3339(),
 *           url:            $this->AbsoluteLink(),
 *           seriesUrl:      $this->Parent()->AbsoluteLink(),
 *           lecturerName:   $event->LecturerName,
 *           attendanceMode: $event->AttendanceMode,
 *           location:       ['name' => $date->VenueName, 'street' => $date->Street, 'city' => $date->City],
 *           price:          (float) $event->Price,
 *           soldOut:        $date->Seats === 0,
 *       ))->getSchemaData();
 *   }
 */
final class EventSchema implements SchemaProvider
{
    /**
     * @param array<string, string> $location
    *   For offline: ['name' => '...', 'street' => '...', 'city' => '...', 'country' => 'BA']
    *   For online:  ['streamUrl' => 'https://...']
     */
    public function __construct(
        private readonly string $title,
        private readonly string $startDate,
        private readonly string $endDate,
        private readonly string $url,
        private readonly string $seriesUrl,
        private readonly string $lecturerName,
        private readonly string $attendanceMode = 'offline', // 'offline'|'online'|'mixed'
        private readonly array  $location       = [],
        private readonly string $description    = '',
        private readonly string $organizerName  = '',
        private readonly float  $price          = 0.0,
        private readonly string $currency       = 'BAM',
        private readonly bool   $soldOut        = false,
        private readonly string $imageUrl       = '',
    ) {}

    public function getSchemaData(): array
    {
        $schema = [
            '@context'            => 'https://schema.org',
            '@type'               => 'EducationEvent',
            '@id'                 => $this->url . '#event',
            'name'                => $this->title,
            'startDate'           => $this->startDate,
            'endDate'             => $this->endDate,
            'eventStatus'         => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => $this->resolveAttendanceMode(),
            'location'            => $this->resolveLocation(),
            'superEvent'          => [
                '@type' => 'EventSeries',
                '@id'   => $this->seriesUrl . '#series',
                'url'   => $this->seriesUrl,
            ],
            'educator' => [
                '@type' => 'Person',
                'name'  => $this->lecturerName,
            ],
            'url' => $this->url,
        ];

        if ($this->description !== '') {
            $schema['description'] = $this->description;
        }

        if ($this->imageUrl !== '') {
            $schema['image'] = $this->imageUrl;
        }

        if ($this->organizerName !== '') {
            $schema['organizer'] = ['@type' => 'Organization', 'name' => $this->organizerName];
        }

        if ($this->price > 0) {
            $schema['offers'] = [
                '@type'         => 'Offer',
                'price'         => number_format($this->price, 2, '.', ''),
                'priceCurrency' => $this->currency,
                'availability'  => $this->soldOut
                    ? 'https://schema.org/SoldOut'
                    : 'https://schema.org/InStock',
                'url'           => $this->url . '#prijava',
            ];
        }

        return $schema;
    }

    private function resolveAttendanceMode(): string
    {
        return match ($this->attendanceMode) {
            'online' => 'https://schema.org/OnlineEventAttendanceMode',
            'mixed'  => 'https://schema.org/MixedEventAttendanceMode',
            default  => 'https://schema.org/OfflineEventAttendanceMode',
        };
    }

    private function resolveLocation(): array
    {
        if ($this->attendanceMode === 'online') {
            return [
                '@type' => 'VirtualLocation',
                'url'   => $this->location['streamUrl'] ?? '',
            ];
        }

        return [
            '@type'   => 'Place',
            'name'    => $this->location['name']    ?? '',
            'address' => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $this->location['street']  ?? '',
                'addressLocality' => $this->location['city']    ?? '',
                'addressCountry'  => $this->location['country'] ?? '',
            ],
        ];
    }
}
