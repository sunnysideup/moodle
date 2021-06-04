<?php

namespace Sunnysideup\Moodle\Api\Users;

use Sunnysideup\Moodle\Api\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;
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
        $this->validateParam($relevantData);
        $data = $this->createData($relevantData);
        $result = parent::runActionInner(['users' => [$data]], 'POST');
        if($result && $result->isSuccess()) {
            return $relevantData->MoodleUid;
        }
        return 0;
    }

    protected function createData($relevantData)
    {
        $data = $this->getConverter()->toMoodle($relevantData, $this->createPassword);
        $data['id'] = $relevantData->MoodleUid;
        return $data;
    }
}
