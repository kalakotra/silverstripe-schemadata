<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData\Contract;

use Kalakotra\SchemaData\SchemaProvider;

/**
 * Implement this on listing/parent page classes that display multiple dates
 * of the same event (for example SeminarListingPage or CourseListingPage).
 *
 * SchemaExtension automatically detects this interface and builds an EventSeries
 * with a nested subEvent array, without additional configuration.
 *
 * Example:
 *
 *   class SeminarListingPage extends Page implements EventSeriesProvider
 *   {
 *       public function getSchemaData(): array
 *       {
 *           return (new EventSeriesSchema(
 *               title: $this->Seminar()->Title,
 *               url:   $this->AbsoluteLink(),
 *               ...
 *           ))->getSchemaData();
 *       }
 *
 *       public function getSubEventProviders(): array
 *       {
 *           return $this->Seminar()->UpcomingDates()->map(
 *               fn($date) => new SeminarSchema(...)
 *           )->toArray();
 *       }
 *   }
 */
interface EventSeriesProvider extends SchemaProvider
{
    /**
    * Returns an array of SchemaProvider instances, one for each subEvent date.
    * SchemaExtension calls getSchemaData() on each item and injects them as the 'subEvent' array.
     *
     * @return SchemaProvider[]
     */
    public function getSubEventProviders(): array;
}
