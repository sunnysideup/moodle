<?php

namespace Sunnysideup\Moodle\Api\Users;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Member;
use Sunnysideup\Moodle\Api\Converters\UserToMoodleUserConversionApi;
use Sunnysideup\Moodle\Api\MoodleAction;

/**
 * class used to respond with JSON requests.
 */
class CreateUser extends MoodleAction
{
    protected $method = 'core_user_create_users';

    protected $createPassword = false;

    protected $resultGetArray = true;

    protected $resultTakeFirstEntry = true;

    protected $resultRelevantArrayKey = 'id';

    protected $resultVariableType = 'int';
    private static $converter = UserToMoodleUserConversionApi::class;

    public function runAction($relevantData)
    {
        if ($this->validateParams($relevantData)) {
            $data = $this->createData($relevantData);
            $result = $this->runActionInner([
                'users' => [$data],
            ], 'POST');

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

        return true;
    }

    protected function createData(Member $relevantData): array
    {
        $data = $this->getConverter()->toMoodle($relevantData, $this->createPassword);
        $data['password'] = $this->randomPassword();

        return $data;
    }

    protected function getConverter()
    {
        $className = $this->config()->get('converter');

        return Injector::inst()->get($className);
    }

    protected function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()';
        $pass = []; //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 23; ++$i) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }

        return implode('', $pass) . 'aA*_'; //turn the array into a string
    }
}
