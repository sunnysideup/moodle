<?php

namespace Sunnysideup\Moodle;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;
use Sunnysideup\Moodle\Users\GetLoginUrlFromEmail;
use Sunnysideup\Moodle\Users\CreateUser;
use Sunnysideup\Moodle\Users\UpdateUser;
use Sunnysideup\Moodle\Users\GetUsers;
use Sunnysideup\Moodle\Courses\GetCourses;

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
        print_r($obj->runAction([]));
    }

    public function syncCourses()
    {
        $existingGpsArray = Group::get()->exclude('MoodleUid', 0)->columnUnique();
        $courses = $api->getCourses();
        foreach($courses as $course) {
            $filter = ['MoodleUid' => $course->ID];
            $gp = Group::get()->filter($filter) ;
            if(! $gp) {
                $gp = Group::create($filter);
            }
            $gp->write();
            unset($existingGpsArray[$gp->ID]);
        }
        $obseleteGroups = Group::get()->filter(['ID' => $existingGpsArray]);
        foreach($obseleteGroups as $group) {
            if($group->MoodleUid) {
                $group->delete();
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
