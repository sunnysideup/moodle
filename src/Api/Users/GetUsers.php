<?php

namespace Sunnysideup\Moodle\Users;

use Sunnysideup\Moodle\Api\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;

use SilverStripe\Security\Member;

class GetUsers Extends MoodleAction
{
    protected $method = 'core_user_get_users_by_field';

    public function runAction($relevantData)
    {
        $this->validateParam($relevantData);

        $params = ['field' => 'email', 'values' => [$relevantData->Email]];

        $result = $this->runActionInner($params);

        return $result;
    }

    protected function validateParam($relevantData)
    {
        if (! $relevantData instanceof Member) {
            user_error('$relevantData is expected to be a '.Member::class);
        }
    }

}
