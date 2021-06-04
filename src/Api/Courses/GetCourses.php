<?php

namespace Sunnysideup\Moodle\Courses;

use Sunnysideup\Moodle\Api\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;

class GetCourses Extends MoodleAction
{

    protected $method = 'core_course_get_courses';

    public function runAction($relevantData)
    {
        $this->validateParam($relevantData);

        $params = [
            'ids'=> $relevantData
        ];
        return $this->runActionInner($params);
    }

    protected function validateParam($relevantData)
    {
        if(! is_array($relevantData)) {
            user_error('$relevantData should be an array');
        }
    }

}
