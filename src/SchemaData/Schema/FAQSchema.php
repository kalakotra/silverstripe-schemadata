<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData\Schema;

use Kalakotra\SchemaData\SchemaProvider;

/**
 * Schema.org/FAQPage
 *
 * Google displays FAQ content directly in search results as an enhanced snippet
 * with questions and answers - one of the most popular rich-result types.
 *
 * Example usage with ElementalArea or has_many FAQ blocks:
 *
 *   public function getSchemaData(): array
 *   {
 *       return (new FAQSchema(
 *           items: $this->FAQItems()->map(
 *               fn($item) => [
 *                   'question' => $item->Question,
 *                   'answer'   => $item->dbObject('Answer')->Plain(), // strip HTML tags
 *               ]
 *           )->toArray(),
 *       ))->getSchemaData();
 *   }
 *
 * Example of manual input:
 *
 *   public function getSchemaData(): array
 *   {
 *       return (new FAQSchema(
 *           items: [
 *               ['question' => 'How do I apply?', 'answer' => 'Use the online form on our website.'],
 *               ['question' => 'What is the price?',   'answer' => 'The price is 250 BAM per person.'],
 *           ],
 *       ))->getSchemaData();
 *   }
 */
final class FAQSchema implements SchemaProvider
{
    /**
     * @param array<int, array{question: string, answer: string}> $items
     */
    public function __construct(
        private readonly array $items,
    ) {}

    public function getSchemaData(): array
    {
        if (empty($this->items)) {
            return [];
        }

        $entities = [];

        foreach ($this->items as $item) {
            if (empty($item['question']) || empty($item['answer'])) {
                continue;
            }

            $entities[] = [
                '@type'          => 'Question',
                'name'           => (string) $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => strip_tags((string) $item['answer']),
                ],
            ];
        }

        if (empty($entities)) {
            return [];
        }

        return [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }
}
