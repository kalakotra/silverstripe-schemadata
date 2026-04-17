<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData\Schema;

use Kalakotra\SchemaData\SchemaProvider;

/**
 * Schema.org/Article or NewsArticle
 *
 * Example usage on BlogPost / NewsPage:
 *
 *   public function getSchemaData(): array
 *   {
 *       return (new ArticleSchema(
 *           title:       $this->Title,
 *           url:         $this->AbsoluteLink(),
 *           datePublished: $this->dbObject('Date')->Rfc3339(),
 *           dateModified:  $this->dbObject('LastEdited')->Rfc3339(),
 *           authorName:  $this->Author()->Name,
 *           description: $this->Summary,
 *           imageUrl:    $this->Image()?->AbsoluteURL() ?? '',
 *           type:        'NewsArticle',
 *       ))->getSchemaData();
 *   }
 */
final class ArticleSchema implements SchemaProvider
{
    public function __construct(
        private readonly string $title,
        private readonly string $url,
        private readonly string $datePublished,
        private readonly string $dateModified    = '',
        private readonly string $authorName      = '',
        private readonly string $authorUrl       = '',
        private readonly string $publisherName   = '',
        private readonly string $publisherLogoUrl = '',
        private readonly string $description     = '',
        private readonly string $imageUrl        = '',
        private readonly string $type            = 'Article', // 'Article'|'NewsArticle'|'BlogPosting'
    ) {}

    public function getSchemaData(): array
    {
        $type = in_array($this->type, ['Article', 'NewsArticle', 'BlogPosting'], true)
            ? $this->type
            : 'Article';

        $data = [
            '@context'      => 'https://schema.org',
            '@type'         => $type,
            'headline'      => $this->title,
            'url'           => $this->url,
            'datePublished' => $this->datePublished,
        ];

        if ($this->dateModified !== '') {
            $data['dateModified'] = $this->dateModified;
        }

        if ($this->description !== '') {
            $data['description'] = $this->description;
        }

        if ($this->imageUrl !== '') {
            $data['image'] = [
                '@type' => 'ImageObject',
                'url'   => $this->imageUrl,
            ];
        }

        if ($this->authorName !== '') {
            $author = ['@type' => 'Person', 'name' => $this->authorName];
            if ($this->authorUrl !== '') {
                $author['url'] = $this->authorUrl;
            }
            $data['author'] = $author;
        }

        if ($this->publisherName !== '') {
            $publisher = ['@type' => 'Organization', 'name' => $this->publisherName];
            if ($this->publisherLogoUrl !== '') {
                $publisher['logo'] = ['@type' => 'ImageObject', 'url' => $this->publisherLogoUrl];
            }
            $data['publisher'] = $publisher;
        }

        return $data;
    }
}
