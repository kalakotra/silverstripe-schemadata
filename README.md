# kalakotra/silverstripe-schemadata

Schema.org JSON-LD generator module for SilverStripe 6.x.

Automatically injects a `<script type="application/ld+json">` block into the page `<head>` based on interface implementation, with no configuration and no assumptions about your model.

---

## Installation

```bash
composer require kalakotra/silverstripe-schemadata
sake dev/build flush=1
```

---

## How It Works

The module adds `SchemaExtension` to `ContentController`. On every request it checks whether the current Page class implements the `SchemaProvider` interface. If it does, it injects JSON-LD into the head. Nothing more.

**You control the data.** Schema classes accept scalar values, not `DataObject` instances. You construct them inside your Page class using data from your own model.

---

## Available Schema Classes

| Class | Schema.org type | Description |
|---|---|---|
| `OrganizationSchema` | Organization | Company / organization |
| `LocalBusinessSchema` | LocalBusiness | Local business with address and geo coordinates |
| `ProductSchema` | Product | Product with price and availability |
| `SeminarSchema` | EducationEvent | Single seminar / education session |
| `EventSeriesSchema` | EventSeries | Listing page with multiple dates of the same event |

---

## Quick Start

### 1. Single JSON-LD block (Product, Organization, LocalBusiness, single event...)

Implement `SchemaProvider` on your Page class:

```php
use Kalakotra\SchemaData\SchemaProvider;
use Kalakotra\SchemaData\Schema\ProductSchema;

class ProductPage extends Page implements SchemaProvider
{
    public function getSchemaData(): array
    {
        return (new ProductSchema(
            title:        $this->Title,
            url:          $this->AbsoluteLink(),
            description:  $this->Summary,
            price:        (float) $this->Price,
            currency:     'BAM',
            availability: $this->InStock ? 'in_stock' : 'out_of_stock',
            sku:          $this->SKU,
        ))->getSchemaData();
    }
}
```

### 2. EventSeries + multiple dates (listing page)

Implement `EventSeriesProvider`, which extends `SchemaProvider`:

```php
use Kalakotra\SchemaData\Contract\EventSeriesProvider;
use Kalakotra\SchemaData\Schema\EventSeriesSchema;
use Kalakotra\SchemaData\Schema\SeminarSchema;

class SeminarListingPage extends Page implements EventSeriesProvider
{
    public function getSchemaData(): array
    {
        $seminar = $this->Seminar();

        return (new EventSeriesSchema(
            title:         $seminar->Title,
            description:   $seminar->Summary,
            url:           $this->AbsoluteLink(),
            organizerName: 'My Company Ltd.',
            lecturerName:  $seminar->LecturerName,
        ))->getSchemaData();
    }

    public function getSubEventProviders(): array
    {
        $seminar = $this->Seminar();

        return $seminar->UpcomingDates()->map(
            fn($date) => new SeminarSchema(
                title:          $seminar->Title,
                startDate:      $date->dbObject('StartDate')->Rfc3339(),
                endDate:        $date->dbObject('EndDate')->Rfc3339(),
                url:            $date->SeminarPage()->AbsoluteLink(),
                seriesUrl:      $this->AbsoluteLink(),
                lecturerName:   $seminar->LecturerName,
                attendanceMode: $seminar->AttendanceMode,
                location: [
                    'name'   => $date->VenueName,
                    'street' => $date->Street,
                    'city'   => $date->City,
                ],
                price:   (float) $seminar->Price,
                soldOut: $date->Seats === 0,
            )
        )->toArray();
    }
}
```

### 3. Single date (child page)

```php
use Kalakotra\SchemaData\SchemaProvider;
use Kalakotra\SchemaData\Schema\SeminarSchema;

class SeminarPage extends Page implements SchemaProvider
{
    public function getSchemaData(): array
    {
        $date    = $this->SeminarDate();
        $seminar = $date->Seminar();

        return (new SeminarSchema(
            title:          $seminar->Title,
            startDate:      $date->dbObject('StartDate')->Rfc3339(),
            endDate:        $date->dbObject('EndDate')->Rfc3339(),
            url:            $this->AbsoluteLink(),
            seriesUrl:      $this->Parent()->AbsoluteLink(),
            lecturerName:   $seminar->LecturerName,
            attendanceMode: $seminar->AttendanceMode,
            location: [
                'name'   => $date->VenueName,
                'street' => $date->Street,
                'city'   => $date->City,
            ],
            price:   (float) $seminar->Price,
            soldOut: $date->Seats === 0,
        ))->getSchemaData();
    }
}
```

---

## SeminarSchema: Location Format

**Offline:**
```php
location: [
    'name'    => 'Venue name',
    'street'  => 'Main Street 1',
    'city'    => 'Sarajevo',
    'country' => 'BA', // default
]
```

**Online:**
```php
attendanceMode: 'online',
location: [
    'streamUrl' => 'https://zoom.us/j/...',
]
```

**Mixed:**
```php
attendanceMode: 'mixed',
location: [
    'name'      => 'Venue name',
    'street'    => 'Main Street 1',
    'city'      => 'Sarajevo',
    'streamUrl' => 'https://zoom.us/j/...',
]
```

---

## Custom Schema Class

You can create any custom schema class, as long as it implements `SchemaProvider`:

```php
use Kalakotra\SchemaData\SchemaProvider;

final class CourseSchema implements SchemaProvider
{
    public function __construct(
        private readonly string $name,
        private readonly string $provider,
        // ...
    ) {}

    public function getSchemaData(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type'    => 'Course',
            'name'     => $this->name,
            'provider' => ['@type' => 'Organization', 'name' => $this->provider],
        ];
    }
}
```

---

## Disable on a Specific Page

Return an empty array from `getSchemaData()` and the module will inject nothing:

```php
public function getSchemaData(): array
{
    if (!$this->ShowSchema) {
        return [];
    }
    // ...
}
```

---

## Compatibility

- PHP 8.2+
- SilverStripe 6.x
- No external dependencies
