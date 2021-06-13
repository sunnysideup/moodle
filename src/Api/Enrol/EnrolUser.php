<?php

namespace Sunnysideup\Moodle\Api\Enrol;

use SilverStripe\Security\Group;
use Sunnysideup\Moodle\Api\MoodleAction;

class EnrolUser extends MoodleAction
{
    /**
     * @var int
     */
    protected const STUDENT_ROLE_ID = 5;

    protected $method = 'enrol_manual_enrol_users';

    protected $resultGetArray = false;

    protected $resultTakeFirstEntry = false;

    protected $resultRelevantArrayKey = '';

    protected $resultVariableType = 'boolean';

    public function runAction($relevantData)
    {
        $Member = null;
        $Group = null;
        if ($this->validateParams($relevantData)) {
            extract($relevantData);
            if ($Member && $Group) {
                $params = [
                    'enrolments' => [
                        [
                            'roleid' => self::STUDENT_ROLE_ID,
                            'userid' => $Member->MoodleUid,
                            'courseid' => $Group->MoodleUid,
                        ],
                    ],
                ];
                $result = $this->runActionInner($params);

                return $this->processResults($result);
            }
        }

        return false;
    }

    protected function validateParams($relevantData): bool
    {
        $result = true;
        if (! is_array($relevantData)) {
            $this->recordValidateParamsError('$relevantData is expected to be an array with two keys (CourseId and UserId).');

            return false;
        }
        if (2 === ! count($relevantData)) {
            $this->recordValidateParamsError('$relevantData is expected to see exactly two parameters. Group and Member.');

            return false;
        }
        if (! isset($relevantData['Group']) && $relevantData['Group'] instanceof Group) {
            $this->recordValidateParamsError('$relevantData is expected to contain an integer for CourseId .');

            return false;
        }
        if (! isset($relevantData['Member']) && $relevantData['Member'] instanceof Group) {
            $this->recordValidateParamsError('$relevantData is expected to contain an integer for UserId .');

            return false;
        }

        return true;
    }
}
