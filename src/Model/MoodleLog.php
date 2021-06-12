<?php

namespace Sunnysideup\Moodle\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class MoodleLog extends DataObject
{
    private static $db = [
        'Action' => 'Varchar',
        'Params' => 'Text',
        'IsSuccess' => 'Boolean',
        'Result' => 'Text',
        'Error' => 'Text',
    ];
    private static $summary_fields = [
        'Action' => 'Action',
        'IsSuccess.Nice' => 'Outcome',
    ];

    private static $default_sort = 'Created DESC';

    private static $has_one = [
        'Member' => Member::class,
    ];

    private static $many_many = [
        'Members' => Member::class,
    ];

    private static $table_name = 'MoodleLog';

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $member = Security::getCurrentUser();
        if ($member) {
            $this->MemberID = Security::getCurrentUser()->ID;
        }
    }

    public function canEdit($member = null)
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return false;
    }
}
