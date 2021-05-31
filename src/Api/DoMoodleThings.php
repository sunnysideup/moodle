<?php

namespace Sunnysideup\Moodle\Api;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Group;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extensible;

class DoMoodleThings
{
    use Injectable;
    use Configurable;
    use Extensible;

    public function getApi()
    {
        return Injector::inst()->get(MoodelRest::class);
    }


    public function syncCourses()
    {
        $existingGpsArray = Group::get()->exclude('MoodleID', 0)->columnUnique();
        $api = $this->getApi();
        $courses = $api->getCourses();
        foreach($courses as $course) {
            $filter = ['MoodleID' => $course->ID];
            $gp = Group::get()->filter($filter) ;
            if(! $gp) {
                $gp = Group::create($filter);
            }
            $gp->write();
            unset($existingGpsArray[$gp->ID]);
        }
        $obseleteGroups = Group::get()->filter(['ID' => $existingGpsArray]);
        foreach($obseleteGroups as $group) {
            if($group->MoodleID) {
                $group->delete();
            }
        }
    }

    public function addUser($member)
    {
        if(! $member->IsRegisteredOnMoodle()) {
            $api = $this->getApi();
        }
    }

    protected $updateUserCount = 0;

    public function updateUser($member)
    {
        $this->updateUserCount++;
        if($member->IsRegisteredOnMoodle()) {
            $api = $this->getApi();
        } elseif($this->updateUserCount < 3) {
            $this->addUser($member);
            $this->updateUser($member);
        }
    }

    public function getGroupFromMoodleCourseId(int $courseId) : ?Group
    {
        return DataObject::get_one(Group::class, ['MoodleID' => $courseId]);
    }

    public function enrolUserOnCourse($member, int $courseId)
    {
        $group = $this->getGroupFromMoodleCourseId($courseId);
        if($group && ! $member->IsRegisteredOnCourse($group)) {
            $api = $this->getApi();
            //todo: add to course on Moodle...
            $this->updateUser($member);
            $group->Members()->add($member);
        }

    }

}
