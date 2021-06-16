<?php

namespace Sunnysideup\Moodle\Api\Users;

use SilverStripe\Security\Member;
use Sunnysideup\Moodle\Api\MoodleAction;

class GetSsoLink extends MoodleAction
{
    protected $method = 'auth_userkey_request_login_url';

    protected $resultGetArray = true;

    protected $resultTakeFirstEntry = false;

    protected $resultRelevantArrayKey = 'loginurl';

    protected $resultVariableType = 'string';

    protected $filterType = 'email';

    protected const FILTER_TYPES_ALLOWED = ['email', 'idnumber',];

    public function setFilterType(string $type) : self
    {
        if( in_array($type, self::FILTER_TYPES_ALLOWED)) {
            $this->filterType = $type;
        } else {
            user_error('Type must be one of: '.print_r(self::FILTER_TYPES_ALLOWED, 1).', "'.$type.'" provided.');
        }
        return $this;
    }

    public function runAction($relevantData)
    {
        if ($this->validateParams($relevantData)) {
            $params = [
                'user' => $this->getFilterStatement($relevantData),
            ];
            $result = $this->runActionInner($params);

            return $this->processResults($result);
        }

        return false;
    }

    protected function validateParams($relevantData): bool
    {
        if (! $relevantData instanceof Member) {
            $this->recordValidateParamsError('We need an ' . Member::class . ' to create this login. You provided: ' . print_r($relevantData, 1));

            return false;
        }
        if ($relevantData->Email && filter_var($relevantData->Email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        $this->recordValidateParamsError('We expect an email here, you provided ' . $relevantData->Email);

        return false;
    }

    protected function getFilterStatement($relevantData) : array
    {
        switch ($this->filterType) {
            case 'idnumber':
                return ['idnumber' => $relevantData->ID,];
                break;
            case 'email':
            default:
                return ['email' => $relevantData->Email,];
        }
    }
}
