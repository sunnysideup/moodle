<?php

namespace Sunnysideup\Moodle\UserOperations;

use Sunnysideup\Moodle\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;

/**
 */
class CreateUser Extends MoodleAction
{

    public function runAction(DataList $members)
    {

        $params = array('userlist' => array(
            (object) array(
                'userid'=>'2',
                'courseid' => '1'
            )
        ));
        return parent::runAction('core_user_create_users', $params);
    }


}
