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
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;


class GroupExtension extends DataExtension
{

    private static $db =[
        'MoodleUid' => 'Int',
    ];

    private const MOODLE_PARENT_GROUP_CODE = 'MOODLES';
    private const MOODLE_PARENT_GROUP_NAME = 'Moodle Groups';
    private const MOODLE_PARENT_GROUP_EXPLANATION = 'This Group holds all the Moodle Course';

    private const MOODLE_NAME_EXTENSION = ' Moodle Gourse Group';

    private const MOODLE_GROUP_EXPLANATION = 'This Group shows the members that are part of a Moodle Course';

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if ($this->owner->MoodleUid) {
            $holderGroup = $this->findOrCreateHolderGroup();
            $this->owner->Locked = true;
            $this->owner->ParentID = $holderGroup->ID;
            if( ! strpos($this->Title, self::MOODLE_NAME_EXTENSION)) {
                $this->owner->Title .= self::MOODLE_NAME_EXTENSION;
            }
            $this->owner->Description = self::MOODLE_GROUP_EXPLANATION;
        }
    }

    public function canDelete($member = null)
    {
        if($this->findOrMoodleCreateHolderGroup()->ID === $this->owner->ID || $this->owner->MoodleUid) {
            return false;
        }
    }

    public function canEdit($member = null)
    {
        if($this->owner->findOrMoodleCreateHolderGroup()->ID === $this->owner->ID) {
            return false;
        } elseif($this->owner->MoodleUid) {
            return false;
        }
    }

    public function findOrMoodleCreateHolderGroup() : Group
    {
        $filter = ['Code' => self::MOODLE_PARENT_GROUP_CODE];
        $group = DataObject::get_one(Group::class, $filter);
        if(! $group) {
            $group =  Group::create($filter);
        }
        $group->Sort = 99999;
        $group->Locked = true;
        $group->Title = self::MOODLE_PARENT_GROUP_NAME;
        $group->Description = self::MOODLE_PARENT_GROUP_EXPLANATION;
        $group->write();

        return DataObject::get_one(Group::class, $filter);
    }

}
