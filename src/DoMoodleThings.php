<?php

namespace Sunnysideup\Moodle;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use Sunnysideup\Moodle\Api\Users\GetLoginUrlFromEmail;
use Sunnysideup\Moodle\Api\Users\CreateUser;
use Sunnysideup\Moodle\Api\Users\UpdateUser;
use Sunnysideup\Moodle\Api\Users\GetUsers;
use Sunnysideup\Moodle\Api\Courses\GetCourses;

use Sunnysideup\Moodle\Model\Extensions\GroupExtension;

class DoMoodleThings
{
    use Injectable;
    use Configurable;
    use Extensible;

    public function getUserSsoLink(?string $email = '') : string
    {
        if(! $email) {
            $member = Security::getCurrentUser();
            if($member) {
                $email = $member->Email;
            }
        }
        $obj = new GetLoginUrlFromEmail();
        return $obj->runAction($email);
    }

    public function getCourses()
    {
        $obj = new GetCourses();
        return $obj->runAction([]);
    }

    public function syncCourses()
    {
        $existingGpsArray = array_flip(Group::get()->exclude('MoodleUid', 0)->columnUnique());
        $courses = $this->getCourses();
        foreach($courses as $course) {
            $group = GroupExtension::create_group_from_moodle_data($course);
            if ($group) {
                unset($existingGpsArray[$group->ID]);
            }
        }
        if(count($existingGpsArray)) {
            $obseleteGroups = Group::get()->filter(['ID' => $existingGpsArray]);
            foreach($obseleteGroups as $group) {
                if($group->MoodleUid) {
                    $group->delete();
                }
            }
        }
    }

    public function addUser(?Member $member = null) : int
    {
        if(! $member) {
            $member = Security::getCurrentUser();
        }
        if($member) {
            if($member->IsRegisteredOnMoodle()) {
                $this->updateUser($member);
            } else {
                $obj = new CreateUser();
                $id = $obj->runAction($member);
                $member->MoodleUid = $id;
                $member->write();
            }
            return (int) $member->MoodleUid;
        }
        return 0;
    }

    protected $updateUserCount = 0;

    public function updateUser($member)
    {
        if(! $member) {
            $member = Security::getCurrentUser();
        }
        if($member) {
            if($member->IsRegisteredOnMoodle()) {
                $obj = new UpdateUser();
                $obj->runAction($member);
            } else {
                $this->createUser($member);
            }
            return (int) $member->MoodleUid;
        }
        return 0;
    }
    public function getUsers($member)
    {
        if(! $member) {
            $member = Security::getCurrentUser();
        }
        if($member) {
            if($member->IsRegisteredOnMoodle()) {
                $obj = new GetUsers();
                return $obj->runAction($member);
            } else {
                return [];
            }
        }
        return [];
    }

    public function getGroupFromMoodleCourseId(int $courseId) : ?Group
    {
        return DataObject::get_one(Group::class, ['MoodleUid' => $courseId]);
    }

    public function enrolUserOnCourse($member, int $courseId)
    {
        $group = $this->getGroupFromMoodleCourseId($courseId);
        if($group && ! $member->IsRegisteredOnCourse($group)) {
            //todo: add to course on Moodle...
            $this->updateUser($member);
            $group->Members()->add($member);
        }

    }

}
