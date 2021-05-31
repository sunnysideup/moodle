<?php

namespace Sunnysideup\Moodle;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;

class MoodleAction {

    use Configurable;

    public function runAction(string $command, $params, ?string $method = 'POST')
    {
        // connect to moodle
        $moodle = MoodleWebservice::connect();
        if(!$moodle) {
            return Debug::message('Failed to connect to Moodle Webservice');
        }

        // create a user list containing one generic user
        $params = array('userlist' => array(
            (object) array(
                'userid'=>'2',
                'courseid' => '1'
            )
        ));

        $outcome = $moodle->call($command, $params, $method);

        return $outcome->Data();
    }
}
