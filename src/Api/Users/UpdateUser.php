<?php

namespace Sunnysideup\Moodle\Api\Users;

use SilverStripe\Security\Member;

class UpdateUser extends CreateUser
{
    protected $method = 'core_user_update_users';

    protected $createPassword = false;

    protected $resultGetArray = false;

    protected $resultTakeFirstEntry = false;

    protected $resultRelevantArrayKey = '';

    protected $resultVariableType = 'int';

    public function runAction($relevantData)
    {
        if ($this->validateParams($relevantData)) {
            $data = $this->createData($relevantData);
            // echo '<h1>Data To Be Send</h1>';
            // print_r($data);
            // user_error('Where do I come from?');
            // die('sdf');
            $result = $this->runActionInner([
                'users' => [$data],
            ], 'POST');
            // echo '<h1>RESULT</h1>';
            // print_r($result);
            if ($result && $result->isSuccess()) {
                return $relevantData->MoodleUid;
            }

            return 0;
        }

        return false;
    }

    protected function createData(Member $relevantData): array
    {
        $data = $this->getConverter()->toMoodle($relevantData, $this->createPassword);
        $data['id'] = $relevantData->MoodleUid;

        return $data;
    }

    protected function validateParams($relevantData): bool
    {
        if (parent::validateParams($relevantData)) {
            if ($relevantData->MoodleUid) {
                return true;
            }
            $this->recordValidateParamsError('This user does not have a MoodleUid.');

            return false;
        }

        return false;
    }
}
