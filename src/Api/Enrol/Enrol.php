<?php

namespace Sunnysideup\Moodle\Api\Enrol;

use Sunnysideup\Moodle\Api\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;


class Enrol Extends MoodleAction
{

    protected $method = 'enrol_manual_enrol_users';

    /**
     * @var int
     */
    protected const STUDENT_ROLE_ID = 5;

    protected $resultGetArray = false;

    protected $resultTakeFirstEntry = false;

    protected $resultRelevantArrayKey = '';

    protected $resultVariableType = 'boolean';

    public function runAction($relevantData)
    {
        $this->validateParam($relevantData);
        $params = [
            [
                'roleid' => self::STUDENT_ROLE_ID,
                'userid' => $relevantData['Member']->MoodleUid,
                'courseid' => $relevantData['Group']->MoodleUid,
            ]
        ];
        $result = $this->runActionInner($params);

        return $this->processResults($result);
    }

    protected function validateParam($relevantData)
    {
        if(! is_array($relevantData)) {
            user_error('$relevantData is expected to be an array with two keys (CourseId and UserId).');
        }
        if (!
            isset($relevantData['Group']) &&
            $relevantData['Group'] instanceof Group
        ) {
            user_error('$relevantData is expected to contain an integer for CourseId .');
        }
        if (!
            isset($relevantData['Member']) &&
            $relevantData['Member'] instanceof Group
        ) {
            user_error('$relevantData is expected to contain an integer for UserId .');
        }

    }

}
