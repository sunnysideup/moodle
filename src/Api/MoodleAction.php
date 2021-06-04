<?php

namespace Sunnysideup\Moodle\Api;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;
use Sunnysideup\Moodle\Model\MoodleLog;
use Sunnysideup\Moodle\Api\MoodleResponse;

abstract class MoodleAction {

    use Configurable;

    protected $method = 'please-set-in-child-class';

    abstract public function runAction($relevantData);

    abstract protected function validateParam($relevantData);

    protected $isQuickMethod = true;


    final protected function runActionInner($params = [], ?string $methodType = 'POST')
    {
        // connect to moodle
        if ($this->isQuickMethod) {
            $call = 'QuickCall';
        } else {
            $call = 'call';
        }
        $id = $this->logCommand($params, $methodType);
        $result = $this->getApi()->$call(
            $this->method,
            $params,
            $methodType
        );
        $this->logOutcome($id, $result);
        return $result;
    }

    final protected function getApi()
    {
        $moodle = MoodleWebservice::connect();
        if(!$moodle) {
            return Debug::message('Failed to connect to Moodle Webservice'.print_r(MoodleWebservice::getErrors(), 1));
        }
        return $moodle;
    }

    protected function logCommand($params, string $methodType) : int
    {
        return MoodleLog::create(
            [
                'Action' => $this->method,
                'Params' => serialize($params),
                'MethodType' => $methodType,
            ]
        )->write();
    }

    protected function logOutcome(int $id, MoodleResponse $result)
    {
        $obj = MoodleLog::get()->byID($id);
        if(! $obj) {
            $obj = new MoodleLog();
        }
        $obj->IsSuccess = $result->isSuccess();
        if($obj->IsSuccess) {
            $obj->Result = serialize($result->getContent());
        } else {
            $obj->Error = $result->getError();
        }
    }
}
