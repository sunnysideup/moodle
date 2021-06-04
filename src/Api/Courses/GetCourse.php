<?php

namespace Sunnysideup\Moodle\Courses;

use Sunnysideup\Moodle\Api\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;

class GetCourse Extends MoodleAction
{

    protected $method = 'core_course_get_courses';

    public function runAction($relevantData)
    {
        $this->validateParam($relevantData);

        $params = array('options' => array(
            (object) array(
                'ids'=> [$courseId]
            )
        ));
        return $this->runActionInner($params);
    }

    protected function validateParam($relevantData)
    {
        if (! $relevantData === intval($relevantData)) {
            user_error('$relevantData is expected to be an integer ');
        }
    }

}
