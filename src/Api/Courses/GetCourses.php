<?php

namespace Sunnysideup\Moodle\Courses;

use Sunnysideup\Moodle\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;

class GetCourses Extends MoodleAction {

    public function runAction(Int $courseId)
    {

        $params = array('options' => array(
            (object) array(
                'ids'=> [$courseId]
            )
        ));
        return parent::runAction('core_course_get_courses', $params);
    }


}
