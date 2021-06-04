<?php

namespace Sunnysideup\Moodle\Api\Courses;

use Sunnysideup\Moodle\Api\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;

class GetCourses Extends MoodleAction
{

    protected $method = 'core_course_get_courses';

    protected $resultGetArray = true;

    protected $resultTakeFirstEntry = false;

    protected $resultRelevantArrayKey = '';

    protected $resultVariableType = 'array';

    public function runAction($relevantData)
    {
        $this->validateParam($relevantData);

        $params = [
            'ids'=> $relevantData
        ];
        $result = $this->runActionInner($params);

        return $this->processResults($result);
    }

    protected function validateParam($relevantData)
    {
        if(! is_array($relevantData)) {
            user_error('$relevantData should be an array');
        }
    }

}
