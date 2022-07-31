<?php

namespace Sunnysideup\Moodle\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

use SilverStripe\Forms\ReadonlyField;
use Exception;

use SilverStripe\Control\Email\Email;
use SilverStripe\Control\Director;

use SilverStripe\Core\Config\Config;
class MoodleLog extends DataObject
{
    private static $db = [
        'Action' => 'Varchar',
        'Params' => 'Text',
        'IsSuccess' => 'Boolean',
        'ErrorEmailSent' => 'Boolean',
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

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if($this->IsSuccess === false) {
            if((bool) $this->ErrorEmailSent === (bool) false) {
                $adminEmail = Config::inst()->get(Email::class, 'admin_email');
                $result = (new Email(
                    $adminEmail,
                    $adminEmail,
                    'Moodle Connection Error',
                    'There was an error in connection with Moodle,
                    see: <a href="'.Director::absoluteURL($this->CMSEditLink()).'">'.Director::absoluteURL($this->CMSEditLink()).'</a>'
                ))->send();
                $this->ErrorEmailSent =$result;
                $this->write();
            }
        }
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldsToTab(
            'Root.Main',
            [
                ReadonlyField::create('Created', 'When')
            ],
            'Action'
        );
        if($this->MemberID) {
            $member = $fields->dataFieldByName('MemberID')
                ->setDescription('<a href="/admin/security/EditForm/field/Members/item/'.$this->MemberID.'/edit">'.$this->Member()->Email.'</a>');
        }
        return $fields;
    }

    public function CMSEditLink() : string
    {
        return '/admin/moodle/Sunnysideup-Moodle-Model-MoodleLog/EditForm/field/Sunnysideup-Moodle-Model-MoodleLog/item/'.$this->ID.'/edit';
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
