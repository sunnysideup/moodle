<?php

namespace Sunnysideup\Moodle\Enrol;

use Sunnysideup\Moodle\Api\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Security\Member;

/**
 */
class Enrol Extends MoodleAction
{

    protected $method = 'core_course_get_courses';

    protected const STUDENT_ROLE_ID = 5;

    public function runAction($relevantData)
    {
        $this->validateParam($relevantData);
        $params = [];
        return $this->runActionInner($params);
    }

    protected function validateParam($relevantData)
    {
        if(! is_array($relevantData)) {
            user_error('$relevantData is expected to be an array with two keys (CourseId and UserId).');
        }
        if (!
            isset($relevantData['CourseId']) &&
            $relevantData['CourseId'] === intval($relevantData['CourseId'])
        ) {
            user_error('$relevantData is expected to contain an integer for CourseId .');
        }
        if (!
            isset($relevantData['UserId']) &&
            $relevantData['UserId'] === intval($relevantData['UserId'])
        ) {
            user_error('$relevantData is expected to contain an integer for UserId .');
        }

    }

}
