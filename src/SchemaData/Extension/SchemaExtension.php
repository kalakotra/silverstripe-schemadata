<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData\Extension;

use Kalakotra\SchemaData\Contract\EventSeriesProvider;
use Kalakotra\SchemaData\SchemaProvider;
use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;

/**
 * Automatically added to ContentController through extensions.yml.
 *
 * Logic:
 *  - If the Page implements EventSeriesProvider, it builds EventSeries + subEvent
 *  - If the Page only implements SchemaProvider, it injects a single JSON-LD block
 *  - Otherwise, it does nothing
 */
class SchemaExtension extends Extension
{
    public function onAfterInit(): void
    {
        $record = $this->getOwner()->data();

        if (!($record instanceof SchemaProvider)) {
            return;
        }

        if ($record instanceof EventSeriesProvider) {
            $this->injectEventSeries($record);
        } else {
            $this->injectSingle($record);
        }
    }

    private function injectEventSeries(EventSeriesProvider $record): void
    {
        $seriesData = $record->getSchemaData();

        if (empty($seriesData)) {
            return;
        }

        $subEvents = array_map(
            fn(SchemaProvider $provider) => $provider->getSchemaData(),
            $record->getSubEventProviders()
        );

        // Filter out empty subEvent blocks
        $seriesData['subEvent'] = array_values(
            array_filter($subEvents, fn(array $data) => !empty($data))
        );

        $this->inject($seriesData, $this->resolveSchemaKey($record) . '-series');
    }

    private function injectSingle(SchemaProvider $record): void
    {
        $data = $record->getSchemaData();

        if (empty($data)) {
            return;
        }

        $this->inject($data, $this->resolveSchemaKey($record));
    }

    private function resolveSchemaKey(SchemaProvider $record): string
    {
        if (method_exists($record, 'getID')) {
            $id = $record->getID();

            if ($id !== null && $id !== '') {
                return (string) $id;
            }
        }

        return spl_object_hash($record);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function inject(array $data, string $key): void
    {
        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        if ($json === false) {
            return;
        }

        Requirements::insertHeadTags(
            sprintf('<script type="application/ld+json">%s</script>', $json),
            'schema-org-' . $key
        );
    }
}
