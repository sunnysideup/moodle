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
        'Created' => 'When',
        'Member.Email' => 'User',
        'Action' => 'Action',
        'IsSuccess.Nice' => 'Success?',
    ];

    private static $default_sort = 'Created DESC';

    private static $has_one = [
        'Member' => Member::class,
    ];

    private static $many_many = [
        'Members' => Member::class,
    ];

    private static $table_name = 'MoodleLog';

    public function canEdit($member = null)
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return false;
    }

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $member = Security::getCurrentUser();
        if ($member && $member->exists()) {
            $this->MemberID = $member->ID;
        }
    }
}
