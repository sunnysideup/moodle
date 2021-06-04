<?php

namespace Sunnysideup\Moodle\Users;

use Sunnysideup\Moodle\Api\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;

class UpdateUser Extends CreateUser
{
    protected $method = 'core_user_update_users';

    protected $createPassword = false;

    protected function createData($relevantData)
    {
        $data = $this->getConverter()->toMoodle($relevantData, $this->createPassword);
        $data['id'] = $relevantData->MoodleUid;
        return $data;
    }
}
