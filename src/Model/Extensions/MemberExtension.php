<?php

namespace Sunnysideup\Moodle\Model\Extensions;

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
}
