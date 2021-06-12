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
        if($this->validateParams($relevantData)) {
            $params = ['field' => 'idnumber', 'values' => [$relevantData->ID]];
            $result = $this->runActionInner($params);
            return $this->processResults($result);
        }
        return false;
    }

    protected function validateParams($relevantData) : bool
    {
        if (! $relevantData instanceof Member) {
            $this->recordValidateParamsError('
                We need an '.Member::class.' to create this login.
                You provided: '.print_r($relevantData, 1));
            return false;
        } else {
            return true;
        }
    }

}
