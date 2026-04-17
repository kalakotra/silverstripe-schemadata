<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData\Schema;

use Kalakotra\SchemaData\SchemaProvider;

/**
 * Schema.org/Person
 *
 * Use for lecturer, author, staff, alumni profile pages, etc.
 *
 * Example usage on StaffPage / LecturerPage:
 *
 *   public function getSchemaData(): array
 *   {
 *       return (new PersonSchema(
 *           name:        $this->Name,
 *           url:         $this->AbsoluteLink(),
 *           jobTitle:    $this->JobTitle,
 *           description: $this->Bio,
 *           imageUrl:    $this->Photo()?->AbsoluteURL() ?? '',
 *           email:       $this->Email,
 *           sameAs:      array_filter([$this->LinkedInURL, $this->TwitterURL]),
 *       ))->getSchemaData();
 *   }
 */
final class PersonSchema implements SchemaProvider
{
    /**
     * @param string[] $sameAs  Array of profile URLs (LinkedIn, Twitter, etc.)
     * @param string[] $knowsAbout  Array of topics/skills
     */
    public function __construct(
        private readonly string $name,
        private readonly string $url           = '',
        private readonly string $jobTitle      = '',
        private readonly string $description   = '',
        private readonly string $imageUrl      = '',
        private readonly string $email         = '',
        private readonly string $phone         = '',
        private readonly string $organization  = '',
        private readonly string $organizationUrl = '',
        private readonly array  $sameAs        = [],
        private readonly array  $knowsAbout    = [],
    ) {}

    public function getSchemaData(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'Person',
            'name'     => $this->name,
        ];

        if ($this->url !== '') {
            $data['url'] = $this->url;
        }

        if ($this->jobTitle !== '') {
            $data['jobTitle'] = $this->jobTitle;
        }

        if ($this->description !== '') {
            $data['description'] = $this->description;
        }

        if ($this->imageUrl !== '') {
            $data['image'] = ['@type' => 'ImageObject', 'url' => $this->imageUrl];
        }

        if ($this->email !== '') {
            $data['email'] = $this->email;
        }

        if ($this->phone !== '') {
            $data['telephone'] = $this->phone;
        }

        if ($this->organization !== '') {
            $affiliation = ['@type' => 'Organization', 'name' => $this->organization];
            if ($this->organizationUrl !== '') {
                $affiliation['url'] = $this->organizationUrl;
            }
            $data['affiliation'] = $affiliation;
        }

        $filteredSameAs = array_values(array_filter($this->sameAs));
        if ($filteredSameAs !== []) {
            $data['sameAs'] = $filteredSameAs;
        }

        $filteredKnowsAbout = array_values(array_filter($this->knowsAbout));
        if ($filteredKnowsAbout !== []) {
            $data['knowsAbout'] = $filteredKnowsAbout;
        }

        return $data;
    }
}
