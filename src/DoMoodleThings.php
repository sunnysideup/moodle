<?php

namespace Sunnysideup\Moodle;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use Sunnysideup\Moodle\Api\Courses\GetCourses;
use Sunnysideup\Moodle\Api\Enrol\EnrolUser;
use Sunnysideup\Moodle\Api\Users\CreateUser;
use Sunnysideup\Moodle\Api\Users\GetLoginUrlFromEmail;
use Sunnysideup\Moodle\Api\Users\GetLoginUrlFromIdNumber;
use Sunnysideup\Moodle\Api\Users\GetUsers;
use Sunnysideup\Moodle\Api\Users\UpdateUser;
use Sunnysideup\Moodle\Api\Users\GetSsoLink;
use Sunnysideup\Moodle\Model\Extensions\GroupExtension;

class DoMoodleThings
{
    use Injectable;
    use Configurable;
    use Extensible;

    public function __construct()
    {
        Environment::setTimeLimitMax(120);
    }

    /**
     * returns SSO link for Moodle for current user or any other email address.
     *
     * @param Member $member optional
     *
     * @return string link
     */
    public function getUserSsoLink(?Member $member = null): string
    {
        $member = $member ?: Security::getCurrentUser();
        if ($member) {
            $email = $member->Email;
            $obj = Injector::inst()->get(GetSsoLink::class);

            return $obj
                ->setFilterType('idnumber')
                ->runAction($member);
        }

        return '';
    }

    /**
     * get an array of all courses on Moodle.
     */
    public function getCourses(): array
    {
        $obj = Injector::inst()->get(GetCourses::class);

        return $obj->runAction([]);
    }

    /**
     * synchronise all Moodle Courses to SS Groups.
     */
    public function syncCourses(): DataList
    {
        $existingGpsArray = array_flip(Group::get()->filter(['CanEnrolWithMoodle' => true])->columnUnique());
        $courses = $this->getCourses();
        foreach ($courses as $course) {
            $group = GroupExtension::create_group_from_moodle_data($course);
            if ($group) {
                unset($existingGpsArray[$group->ID]);
            }
        }
        if (count($existingGpsArray)) {
            $obseleteGroups = Group::get()->filter(['ID' => $existingGpsArray]);
            foreach ($obseleteGroups as $group) {
                if ($group->MoodleUid) {
                    $group->delete();
                }
            }
        }

        return Group::get()->filter(['CanEnrolWithMoodle' => true]);
    }

    /**
     * add user in Moodle from Member
     * returns the MoodleUid assigned to the user.
     *
     * @param ?Member $member
     */
    public function addUser(?Member $member = null): int
    {
        return $this->addOrUpdate($member, true);
    }

    /**
     * update the user details on Moodle.
     * returns the MoodleUid assigned to the user.
     *
     * @param Member $member
     * @param bool   $createMemberIfDoesNotExist
     *
     * @return int MoodleID
     */
    public function updateUser(?Member $member = null, ?bool $createMemberIfDoesNotExist = true): int
    {
        return $this->addOrUpdate($member, $createMemberIfDoesNotExist);
    }

    public function addOrUpdate(?Member $member = null, ?bool $createMemberIfDoesNotExist = true): int
    {
        $member = $member ?: Security::getCurrentUser();
        if ($member) {
            if ($this->IsRegisteredOnMoodleWithCheck($member)) {
                $obj = Injector::inst()->get(UpdateUser::class);
                $obj->runAction($member);
            } else {
                if($this->RecoverMemberFromEmail($member) || $this->RecoverMemberFromUsername($member)) {
                    $obj = Injector::inst()->get(UpdateUser::class);
                    $obj->runAction($member);
                } elseif ($createMemberIfDoesNotExist) {
                    $obj = Injector::inst()->get(CreateUser::class);
                    $id = $obj->runAction($member);
                    if ($id && (int) $id === $id) {
                        $member->MoodleUid = $id;
                        $member->write();
                    }
                }
            }

            return (int) $member->MoodleUid;
        }

        return 0;
    }

    public function getUsers($member, ?string $moodleFieldName = 'idnumber', ?string $silverstripeFieldName = 'ID')
    {
        $obj = Injector::inst()->get(GetUsers::class);

        return $obj->runAction($member, $moodleFieldName, $silverstripeFieldName);
    }

    public function getGroupFromMoodleCourseId(int $courseId): ?Group
    {
        return DataObject::get_one(Group::class, ['MoodleUid' => $courseId]);
    }

    public function enrolUserOnCourse(Group $group, ?Member $member = null): bool
    {
        $outcome = false;
        $member = $member ?: Security::getCurrentUser();
        if ($member) {
            $outcome = false;
            if ($group && $group->MoodleUid) {
                $outcome = true;
                if (! $member->IsRegisteredOnCourse($group)) {
                    $obj = Injector::inst()->get(EnrolUser::class);
                    $outcome = $obj->runAction(['Member' => $member, 'Group' => $group]);
                    if ($outcome) {
                        $group->Members()->add($member);
                    }
                }
            }
            $this->updateUser($member);
        }

        return $outcome;
    }

    /**
     * returns true if Member has been set to corresponding Moodle Member
     * @param  Member $member
     * @return bool
     */
    public function IsRegisteredOnMoodleWithCheck(Member $member) : bool
    {
        if($member->IsRegisteredOnMoodle()) {
            return $this->IsRegisteredOnMoodleWithCheckInner($member);
        }

        return false;
    }

    /**
     * returns true if Member has been set to corresponding Moodle Member
     * @param  Member $member
     * @return bool
     */
    public function RecoverMemberFromEmail(Member $member) : bool
    {
        return $this->IsRegisteredOnMoodleWithCheckInner($member, 'email', 'Email');
    }

    /**
     * returns true if Member has been set to corresponding Moodle Member
     * @param  Member $member
     * @return bool
     */
    public function RecoverMemberFromUsername(Member $member) : bool
    {
        return $this->IsRegisteredOnMoodleWithCheckInner($member, 'username', 'getMoodleUsername');
    }

    public function IsRegisteredOnMoodleWithCheckInner(Member $member, ?string $moodleFieldName = 'idnumber', ?string $silverstripeFieldNameOrMethod = 'ID') : bool
    {
        // value from SilverStripe
        if($member->hasMethod($silverstripeFieldNameOrMethod)) {
            $valueFromSilvertripe = $member->$silverstripeFieldNameOrMethod();
        } else {
            $valueFromSilvertripe = $member->$silverstripeFieldNameOrMethod;
        }
        // value from Moodle
        $array = $this->getUsers($member, $moodleFieldName, $silverstripeFieldNameOrMethod);
        $valueFromMoodle = $array[$moodleFieldName] ?? '';
        if( $valueFromMoodle == $valueFromSilvertripe) {
            if(! $member->MoodleUid) {
                $member->MoodleUid = $array['id'];
                $member->write();
            }
            return true;
        } else {
            // reset member
            if($member->MoodleUid) {
                $member->MoodleUid = 0;
                $member->write();
            }
        }

        return false;
    }
}
