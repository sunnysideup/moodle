<?php

namespace Sunnysideup\Moodle\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class MoodleLog extends DataObject
{

    private static $db = [
        'Action' => 'Varchar',
        'Params' => 'Text',
        'IsSuccess' => 'Boolean',
        'Result' => 'Text',
        'Error' => 'Text',
    ];

    private static $default_sort = 'Created DESC';

    private static $has_one = [
        'Member' => Member::class,
    ];

    private static $many_many = [
        'Members' => Member::class,
    ];

    private static $table_name = 'MoodleLog';

}
