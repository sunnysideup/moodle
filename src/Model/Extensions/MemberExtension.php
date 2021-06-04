<?php
namespace Sunnysideup\Moodle\Model\Extensions;

use SilverStripe\Forms\TextareaField;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\Filters\ExactMatchFilter;
use SilverStripe\ORM\Filters\PartialMatchFilter;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;

class MemberExtension extends DataExtension
{

    private static $db =[
        'MoodleUid' => 'Int',
    ];

    private static $indexes =[
        'MoodleUid' => true,
    ];

    public function IsRegisteredOnMoodle() : bool
    {
        return $this->owner->MoodleUid ? true : false;
    }

    public function IsRegisteredOnCourse(Group $group) : bool
    {
        return $this->owner->Groups()->filter(['MemberID' => $this->owner->ID])->count() > 0;
    }

}
