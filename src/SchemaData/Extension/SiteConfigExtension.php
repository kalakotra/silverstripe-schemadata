<?php

namespace Kalakotra\SchemaData\Extension;

use SilverStripe\Core\Extension;
use SilverStripe\Control\Director;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;

class SiteConfigExtension  extends Extension
{
    private static $db = [
        'OrganizationName' => 'Varchar',
        'OrganisationURL' => 'Varchar',
        'OrganizationLogoURL' => 'Varchar',
        'OrganizationContactPointEmail' => 'Varchar',
        'OrganizationContactPointPhone' => 'Varchar',
    ];

    public function getOrganizationName()
    {
        $organizationName = $this->owner->hasField('OrganizationName')
            ? (string) $this->owner->getField('OrganizationName')
            : '';

        return $organizationName !== ''
            ? $organizationName
            : (string) $this->owner->Title;
    }

    public function getOrganisationURL()
    {
        $organisationUrl = $this->owner->hasField('OrganisationURL')
            ? (string) $this->owner->getField('OrganisationURL')
            : '';

        return $organisationUrl !== ''
            ? $organisationUrl
            : Director::absoluteBaseURL();
    }

    public function getOrganizationLogoURL()
    {
        return $this->owner->hasField('OrganizationLogoURL')
            ? (string) $this->owner->getField('OrganizationLogoURL')
            : '';
    }

    public function getOrganizationContactPointEmail()
    {
        return $this->owner->hasField('OrganizationContactPointEmail')
            ? (string) $this->owner->getField('OrganizationContactPointEmail')
            : '';
    }

    public function getOrganizationContactPointPhone()
    {
        return $this->owner->hasField('OrganizationContactPointPhone')
            ? (string) $this->owner->getField('OrganizationContactPointPhone')
            : '';
    }

    /**
     * Update CMS fields.
     *
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.SchemaData', [
            TextField::create('OrganizationName', 'Organization Name'),
            TextField::create('OrganisationURL', 'Organization URL'),
            TextField::create('OrganizationLogoURL', 'Organization Logo URL'),
            TextField::create('OrganizationContactPointEmail', 'Organization Contact Point Email'),
            TextField::create('OrganizationContactPointPhone', 'Organization Contact Point Phone'),
        ]);
        return $fields;
    }
}