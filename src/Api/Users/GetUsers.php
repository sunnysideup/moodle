<?php

namespace Sunnysideup\Moodle\Api\Users;

use Sunnysideup\Moodle\Api\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;

use SilverStripe\Security\Member;
use Sunnysideup\Moodle\Api\Converters\UserToMoodleUserConversionApi;
class GetUsers Extends MoodleAction
{
    protected $method = 'core_user_get_users_by_field';

    protected $resultGetArray = true;

    protected $resultTakeFirstEntry = true;

    protected $resultRelevantArrayKey = '';

    protected $resultVariableType = 'array';

    public function runAction($relevantData)
    {
        $this->validateParam($relevantData);

        $params = ['field' => 'email', 'values' => [$relevantData->Email]];

        $result = $this->runActionInner($params);

        return $this->processResults($result);
    }

    protected function validateParam($relevantData)
    {
        if (! $relevantData instanceof Member) {
            user_error('$relevantData is expected to be a '.Member::class);
        }
    }

}
