<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData\Schema;

use Kalakotra\SchemaData\Contract\EventSeriesProvider;
use Kalakotra\SchemaData\SchemaProvider;

/**
 * Schema.org/EventSeries for a listing page with multiple dates.
 *
 * Implement EventSeriesProvider on your listing page class and
 * SchemaExtension will automatically inject the subEvent array from getSubEventProviders().
 *
 * Example usage on SeminarListingPage:
 *
 *   class SeminarListingPage extends Page implements EventSeriesProvider
 *   {
 *       public function getSchemaData(): array
 *       {
 *           $seminar = $this->Seminar();
 *           return (new EventSeriesSchema(
 *               title:         $seminar->Title,
 *               description:   $seminar->Summary,
 *               url:           $this->AbsoluteLink(),
 *               organizerName: 'My Company Ltd.',
 *               lecturerName:  $seminar->LecturerName,
 *           ))->getSchemaData();
 *       }
 *
 *       public function getSubEventProviders(): array
 *       {
 *           $seminar = $this->Seminar();
 *           return $seminar->UpcomingDates()->map(
 *               fn($date) => new SeminarSchema(
 *                   title:          $seminar->Title,
 *                   startDate:      $date->dbObject('StartDate')->Rfc3339(),
 *                   endDate:        $date->dbObject('EndDate')->Rfc3339(),
 *                   url:            $date->SeminarPage()->AbsoluteLink(),
 *                   seriesUrl:      $this->AbsoluteLink(),
 *                   lecturerName:   $seminar->LecturerName,
 *                   attendanceMode: $seminar->AttendanceMode,
 *                   location:       ['name' => $date->VenueName, 'street' => $date->Street, 'city' => $date->City],
 *                   price:          (float) $seminar->Price,
 *                   soldOut:        $date->Seats === 0,
 *               )
 *           )->toArray();
 *       }
 *   }
 */
final class EventSeriesSchema implements EventSeriesProvider
{
    /**
     * @param SchemaProvider[] $subEventProviders
     */
    public function __construct(
        private readonly string $title,
        private readonly string $url,
        private readonly string $description    = '',
        private readonly string $organizerName  = '',
        private readonly string $lecturerName   = '',
        private readonly string $imageUrl       = '',
        private readonly array  $subEventProviders = [],
    ) {}

    public function getSchemaData(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'EventSeries',
            '@id'      => $this->url . '#series',
            'name'     => $this->title,
            'url'      => $this->url,
        ];

        if ($this->description !== '') {
            $data['description'] = $this->description;
        }

        if ($this->imageUrl !== '') {
            $data['image'] = $this->imageUrl;
        }

        if ($this->organizerName !== '') {
            $data['organizer'] = ['@type' => 'Organization', 'name' => $this->organizerName];
        }

        if ($this->lecturerName !== '') {
            $data['educator'] = ['@type' => 'Person', 'name' => $this->lecturerName];
        }

        if ($this->subEventProviders !== []) {
            $data['subEvent'] = array_values(array_filter(
                array_map(fn($p) => $p->getSchemaData(), $this->subEventProviders),
                fn(array $d) => $d !== []
            ));
        }

        return $data;
    }

    /**
     * @return SchemaProvider[]
     */
    public function getSubEventProviders(): array
    {
        return $this->subEventProviders;
    }
}
