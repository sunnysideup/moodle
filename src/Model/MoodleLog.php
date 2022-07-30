<?php

namespace Sunnysideup\Moodle\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use Exception;
class MoodleLog extends DataObject
{
    private static $db = [
        'Action' => 'Varchar',
        'Params' => 'Text',
        'IsSuccess' => 'Boolean',
        'Result' => 'Text',
        'Error' => 'Text',
        'ErrorMessage' => 'Varchar',
    ];
    private static $summary_fields = [
        'Created' => 'When',
        'Member.Email' => 'User',
        'Action' => 'Action',
        'IsSuccess.Nice' => 'Success?',
    ];

    private static $default_sort = 'Created DESC';

    private static $has_one = [
        'Member' => Member::class,
    ];

    private static $many_many = [
        'Members' => Member::class,
    ];

    private static $table_name = 'MoodleLog';

    public function canEdit($member = null)
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return false;
    }

    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();
        // add member if no member present
        if(! $this->MemberID) {
            $member = Security::getCurrentUser();
            if ($member && $member->exists()) {
                $this->MemberID = $member->ID;
            }
        }

        // remove passwords
        try {
            if($this->Params) {
                if(strpos($this->Params, 'password')) {
                    $array = unserialize($this->Params);
                    $array = $this->removeKey($array, 'Password');
                    $this->Params = serialize($array);
                }
            }

        } catch (Exception $e) {
            echo '<h2>error with MoodleLog #'.$this->ID.'</h2>';
            print_r($this->Params);
        }

        // retrieve actual error
        try {
            if($this->Error && ! $this->ErrorMessage) {
                $string = unserialize($this->Error);
                $debuginfoArray = json_decode($string, true);
                $this->ErrorMessage = $debuginfoArray['debuginfo'] ?? '';
            }
        } catch (Exception $e) {
            echo '<h2>error with MoodleLog #'.$this->ID.'</h2>';
            print_r($this->Error);
            //do nothing
        }

    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab(
            'Root.Main',
            [
                ReadonlyField::create('Created')
            ],
            'Action'
        );
        $fields->addFieldsToTab(
            'Root.Errors',
            [
                LiteralField::create(
                    'Review all errors',
                    '<a href="/dev/tasks/Sunnysideup-Moodle-Model-MoodleLogErrorList">Review all errors in one go</a>'
                )
            ],
        );
        if($this->MemberID) {
            $member = $fields->dataFieldByName('MemberID')
                ->setDescription('<a href="/admin/security/EditForm/field/Members/item/'.$this->MemberID.'/edit">'.$this->Member()->Email.'</a>');
        }
        return $fields;
    }

    protected function removeKey(array $array, string $key)
    {
        if(is_array($array)) {
            if (array_key_exists($key, $array)) {
                unset($array[$key]);
            }

            foreach ($array as $element) {
                if (is_array($element)) {
                    $this->removeKey($element, $key);
                }

            }
        }
    }
}
