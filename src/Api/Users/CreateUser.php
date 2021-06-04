<?php

namespace Sunnysideup\Moodle\Users;

use Sunnysideup\Moodle\Api\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Member;

/**
 * class used to respond with JSON requests
 *
 *
 */
class CreateUser Extends MoodleAction
{
    protected $method = 'core_user_create_users';

    private static $converter = UserToMoodleUserConversionApi::class;

    protected $createPassword = false;

    public function runAction($relevantData)
    {
        $this->validateParam($relevantData);
        $data = $this->createData($relevantData);
        $result = parent::runActionInner(['users' => [$data]], 'POST');
        if($result && $result->isSuccess()) {
            $array = $result->getContentAsArray();
            return $array[0]['id'] ?? 0;
        }
        return 0;
    }

    protected function validateParam($relevantData)
    {
        if (! $relevantData instanceof Member) {
            user_error('$relevantData is expected to be a '.Member::class);
        }
    }

    protected function createData($relevantData)
    {
        $data = $this->getConverter()->toMoodle($relevantData, $this->createPassword);
        $data['password'] = $this->randomPassword();
        $data['username'] = str_replace('@', '_', $relevantData->Email);
        $data['username'] = str_replace('.', '_', $relevantData->Email);
        return $data;
    }

    protected function getConverter()
    {
        $className = $this->config()->get('converter');
        return Injector::inst()->get($className);
    }

    protected function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 23; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass).'aA*_'; //turn the array into a string
    }

}
