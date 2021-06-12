<?php

namespace Sunnysideup\Moodle\Api\Enrol;

use Sunnysideup\Moodle\Api\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;


class EnrolUser Extends MoodleAction
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
        if($this->validateParams($relevantData)) {
            extract($relevantData);
            $params = [
                'enrolments' => [
                    [
                        'roleid' => self::STUDENT_ROLE_ID,
                        'userid' => $Member->MoodleUid,
                        'courseid' => $Group->MoodleUid,
                    ]
                ]
            ];
            $result = $this->runActionInner($params);
            return $this->processResults($result);
        }
        return false;
    }

    protected function validateParams($relevantData) : bool
    {
        $result = true;
        if(! is_array($relevantData)) {
            $this->recordValidateParamsError('$relevantData is expected to be an array with two keys (CourseId and UserId).');
            return false;
        }
        elseif(! count($relevantData) == 2) {
            $this->recordValidateParamsError('$relevantData is expected to see exactly two parameters. Group and Member.');
            return false;
        }
        elseif (! isset($relevantData['Group']) && $relevantData['Group'] instanceof Group) {
            $this->recordValidateParamsError('$relevantData is expected to contain an integer for CourseId .');
            return false;
        }
        elseif (! isset($relevantData['Member']) && $relevantData['Member'] instanceof Group) {
            $this->recordValidateParamsError('$relevantData is expected to contain an integer for UserId .');
            return false;
        } else {
            return true;
        }

    }

}
