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

    protected $isQuickMethod = true;

    protected $resultGetArray = true;

    protected $resultTakeFirstEntry = false;

    protected $resultRelevantArrayKey = '';

    protected $resultVariableType = 'string';

    abstract public function runAction($relevantData);

    abstract protected function validateParam($relevantData);

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

    protected function processResults($result)
    {
        if($result->isSuccess()) {
            if($this->resultGetArray) {
                $result = $result->getContentAsArray();
                if($this->resultTakeFirstEntry) {
                    $result = $result[0] ?? [];
                }
                if($this->resultRelevantArrayKey) {
                    $result = $result[$this->resultRelevantArrayKey] ?? '';
                }
            }
        } else {
            $result = '';
        }
        switch (strtolower($this->resultVariableType)) {
            case 'int':
            case 'integer':
                $result = (int) $result;
                break;
            case 'array':
                if(! is_array($result)) {
                    if($result) {
                        $result = [$result];
                    } else {
                        $result = [];
                    }
                }
                break;
            case 'string':
            default:
                $result = (string) $result;
                break;
        }
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
