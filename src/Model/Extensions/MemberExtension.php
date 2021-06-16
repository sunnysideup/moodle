<?php

namespace Sunnysideup\Moodle\Model\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Group;

class MemberExtension extends DataExtension
{
    private static $db = [
        'MoodleUid' => 'Int',
    ];

    private static $indexes = [
        'MoodleUid' => true,
    ];

    public function IsRegisteredOnMoodle(): bool
    {
        return (bool) $this->owner->MoodleUid;
    }

    public function IsRegisteredOnCourse(Group $group): bool
    {
        return $this->owner->Groups()->filter(['ID' => $group->ID])->count() > 0;
    }

     /**
     * Update Fields.
     *
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            'Root.Moodle',
            [
                $fields->dataFieldByName('MoodleUid')->setReadOnly(true),
            ],
        );

        return $fields;
    }
}
