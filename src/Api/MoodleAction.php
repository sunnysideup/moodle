<?php

namespace Sunnysideup\Moodle\Api;

use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use Sunnysideup\Moodle\Api\MoodleResponse;
use Sunnysideup\Moodle\Api\MoodleWebservice;
use Sunnysideup\Moodle\Model\MoodleLog;

abstract class MoodleAction {

    use Configurable;

    use Injectable;

    private static $moodle_service_provider = MoodleWebservice::class;

    private static $log = true;

    protected $method = 'please-set-in-child-class';

    protected $isQuickMethod = true;

    protected $resultGetArray = true;

    protected $resultTakeFirstEntry = false;

    protected $resultRelevantArrayKey = '';

    protected $resultVariableType = 'string';

    protected $paramValidationErrors = [];

    abstract public function runAction($relevantData);

    public function validateParamsOnly($relevantData) : bool
    {
        return $this->validateParams($relevantData);
    }

    public function recordValidateParamsError(?string $string = 'error')
    {
        $this->logCommand($string, 'PARAMS_ERROR');
    }

    public function getParamValidationErrors() : array
    {
        return $this->paramValidationErrors;
    }

    abstract protected function validateParams($relevantData) : bool;

    final protected function runActionInner($params = [], ?string $methodType = 'POST')
    {
        $call = $this->isQuickMethod ? 'QuickCall' : 'call';
        $id = $this->logCommand($params, $methodType);
        $result = $this->getApi()->$call(
            $this->method,
            $params,
            $methodType
        );
        if($result instanceof MoodleResponse) {
            $this->logOutcome($id, $result);
        }
        return $result;
    }

    protected function processResults($result)
    {
        $success = false;
        if($result->isSuccess()) {
            $success = true;
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
        if($result instanceof MoodleResponse) {
            $result = $result->getContent();
        }
        switch (strtolower($this->resultVariableType)) {
            case 'int':
            case 'integer':
                $result = (int) $result;
                break;
            case 'array':
                if(! is_array($result)) {
                    $result = $result ? [$result] : [];
                }
                break;
            case 'boolean':
                $result = $success;
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
        $className = $this->Config()->get('moodle_service_provider');
        $moodle = $className::connect();
        if(! $moodle) {
            return Debug::message('Failed to connect to Moodle Webservice'.print_r($className::getErrors(), 1));
        }
        return $moodle;
    }

    protected function logCommand($params, string $methodType) : int
    {
        if($this->Config()->get('log')) {
            return MoodleLog::create(
                [
                    'Action' => $this->method,
                    'Params' => serialize($params),
                    'MethodType' => $methodType,
                ]
            )->write();
        }
        return 0;
    }

    protected function logOutcome(int $id, MoodleResponse $result)
    {
        if($this->Config()->get('log')) {
            $obj = MoodleLog::get()->byID($id);
            if(! $obj) {
                $obj = new MoodleLog();
            }
            $obj->IsSuccess = $result->isSuccess();
            if($obj->IsSuccess) {
                $obj->Result = serialize($result->getContent());
            } else {
                $obj->Error = serialize($result->getError());
            }
            $obj->write();
        }
    }
}
