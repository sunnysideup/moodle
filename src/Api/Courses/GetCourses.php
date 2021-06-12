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
        if($this->validateParams($relevantData)) {

            $params = [
                'ids'=> $relevantData
            ];
            $result = $this->runActionInner($params);

            return $this->processResults($result);
        }
        return false;
    }

    protected function validateParams($relevantData) : bool
    {
        if(! is_array($relevantData)) {
            $this->recordValidateParamsError('$relevantData should be an array');
            return false;
        } else {
            return true;
        }
    }

}
