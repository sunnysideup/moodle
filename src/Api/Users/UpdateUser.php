<?php

namespace Sunnysideup\Moodle\Api\Users;

use Sunnysideup\Moodle\Api\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Security\Member;
use Sunnysideup\Moodle\Api\Converters\UserToMoodleUserConversionApi;

class UpdateUser Extends CreateUser
{
    protected $method = 'core_user_update_users';

    protected $createPassword = false;

    protected $resultGetArray = false;

    protected $resultTakeFirstEntry = false;

    protected $resultRelevantArrayKey = '';

    protected $resultVariableType = 'int';

    public function runAction($relevantData)
    {
        if($this->validateParams($relevantData)) {
            $data = $this->createData($relevantData);
            $result = $this->runActionInner(['users' => [$data]], 'POST');
            if($result && $result->isSuccess()) {
                return $relevantData->MoodleUid;
            } else {
                return 0;
            }
        }
        return false;
    }

    protected function createData(Member $relevantData) : array
    {
        $data = $this->getConverter()->toMoodle($relevantData, $this->createPassword);
        $data['id'] = $relevantData->MoodleUid;
        return $data;
    }

    protected function validateParams($relevantData) : bool
    {
        if( parent::validateParams($relevantData)) {
            if($relevantData->MoodleUid) {
                return true;
            } else {
                $this->paramValidationErrors[] = 'This user does not have a MoodleUid.';
                return false;
            }
        }
        return false;
    }
}
