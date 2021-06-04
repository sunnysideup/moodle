<?php

namespace Sunnysideup\Moodle\Users;

use Sunnysideup\Moodle\Api\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;

class GetLoginUrlFromEmail Extends MoodleAction
{
    protected $method = 'auth_userkey_request_login_url';

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
        if($result && $result->isSuccess()) {
            $array = $result->getContentAsArray();
            return $array['loginurl'] ?? '';
        }
        return 'error';

    }

    protected function validateParam($relevantData)
    {
        if($relevantData && ! filter_var($relevantData, FILTER_VALIDATE_EMAIL)) {
            user_error('We expect an email here.');
        }
    }


}
