<?php

namespace Sunnysideup\Moodle\Api\Users;

use SilverStripe\ORM\ArrayList;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

use Sunnysideup\Moodle\Api\MoodleAction;

class GetLoginUrlFromEmail Extends MoodleAction
{
    protected $method = 'auth_userkey_request_login_url';

    protected $resultGetArray = true;

    protected $resultTakeFirstEntry = false;

    protected $resultRelevantArrayKey = 'loginurl';

    protected $resultVariableType = 'string';

    public function runAction($relevantData)
    {
        if($this->validateParams($relevantData)) {
            $params= [
                'user' => [
                    'email' => $relevantData->Email,
                ]
            ];
            $result = $this->runActionInner($params);
            return $this->processResults($result);
        }
        return false;
    }

    protected function validateParams($relevantData) : bool
    {
        if(! $relevantData instanceof Member) {
            $this->recordValidateParamsError('We need an '.Member::class.' to create this login. You provided: '.print_r($relevantData, 1));
            return false;
        }
        if($relevantData->Email && filter_var($relevantData->Email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            $this->recordValidateParamsError('We expect an email here, you provided ' . $relevantData->Email);
            return false;
        }
    }


}
