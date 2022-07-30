<?php

namespace Sunnysideup\Moodle\Model\Extensions;

use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Group;

use SilverStripe\Core\Injector\Injector;

use Sunnysideup\Moodle\DoMoodleThings;

class MemberExtension extends DataExtension
{
    private static $db = [
        'MoodleUid' => 'Int',
    ];

    private static $indexes = [
        'MoodleUid' => true,
    ];

    private static $casting = [
        'MoodleUsername' => 'Varchar',
        'IsRegisteredOnMoodle' => 'Boolean',
    ];


    public function MoodleUsername() : string
    {
        return $this->getMoodleUsername();
    }

    public function getMoodleUsername() : string
    {
        $owner = $this->getOwner();
        $username = $owner->FirstName. ' ' . $owner->Surname;
        $username = preg_replace("/[^A-Za-z0-9]/", '_', $username);
        return strtolower(substr($username, 0, 20) . '_' . $owner->ID);
    }

    public function IsRegisteredOnMoodle(): bool
    {
        return $this->getIsRegisteredOnMoodle();
    }

    public function getIsRegisteredOnMoodle(): bool
    {
        return (bool) $this->getOwner()->MoodleUid;
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
        if(isset($_GET['reconnect'])) {
            $obj = Injector::inst()->get(DoMoodleThings::class);
            $obj->addUser($this->getOwner());
        }
        $fields->addFieldsToTab(
            'Root.Moodle',
            [
                ReadonlyField::create(
                    'IsRegisteredOnMoodleNice',
                    'Is Registered On Moodle',
                    $this->getOwner()->IsRegisteredOnMoodle() ? 'YES' : 'NO'
                ),
                ReadonlyField::create(
                    'MoodleUsername',
                    'Moodle Username'
                )
                    ->setDescription('May not be set, but that is how we would register it.'),

                $fields->dataFieldByName('MoodleUid')->setReadOnly(true),
            ],
        );

        return $fields;
    }

}
