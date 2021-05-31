<?php

namespace Sunnysideup\Moodle\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class MoodleLog extends DataObject
{

    private static $db = [
        'Action' => 'Varchar',
        'Variables' => 'Text',
    ];

    private static $has_one = [
        'Member' => Member::class,
    ];

    private static $many_many = [
        'Members' => Member::class,
    ];

}
