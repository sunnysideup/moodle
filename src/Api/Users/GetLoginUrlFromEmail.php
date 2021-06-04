<?php

namespace Sunnysideup\Moodle\Api\Users;

use Sunnysideup\Moodle\Api\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;

class GetLoginUrlFromEmail Extends MoodleAction
{
    protected $method = 'auth_userkey_request_login_url';

    protected $resultGetArray = true;

    protected $resultTakeFirstEntry = false;

    protected $resultRelevantArrayKey = 'loginurl';

    protected $resultVariableType = 'string';

    public function runAction($relevantData)
    {
        if(! $relevantData) {
            return '';
        }
        $this->validateParam($relevantData);
        $params= [
            'user' => [
                'email' => $relevantData,
            ]
        ];
        $result = parent::runActionInner($params);

        return $this->processResults($result);

    }

    protected function validateParam($relevantData)
    {
        if($relevantData && ! filter_var($relevantData, FILTER_VALIDATE_EMAIL)) {
            user_error('We expect an email here.');
        }
    }


}
