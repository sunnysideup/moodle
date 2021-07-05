<?php

namespace Sunnysideup\Moodle\Api\Converters;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Security\Member;

class UserToMoodleUserConversionApi
{
    use Configurable;
    use Injectable;

    private static $conversion_array = [
        // 'password' => ''
        [
            'SilverstripeField' => 'ID',
            'MoodleField' => 'idnumber',
            'Type' => 'string',
        ],
        [
            'SilverstripeField' => 'FirstName',
            'MoodleField' => 'firstname',
            'Type' => 'string',
        ],
        [
            'SilverstripeField' => 'Surname',
            'MoodleField' => 'lastname',
            'Type' => 'string',
        ],
        [
            'SilverstripeField' => 'Email',
            'MoodleField' => 'email',
            'Type' => 'string',
        ],
    ];

    private static $custom_fields = [];

    public function toMoodle(Member $member, ?bool $createPassword = true)
    {
        $returnArray = [];
        foreach ($this->config()->get('conversion_array') as $details) {
            $ssField = $details['SilverstripeField'];
            $moodleField = $details['MoodleField'];
            $type = $details['Type'];
            $returnArray[$moodleField] = $this->getValueForMoodle($member, $ssField, $type);
        }
        //fix username
        $returnArray['username'] = $this->createUserName($member);
        $array['customfields'] = [];
        foreach ($this->config()->get('custom_fields') as $details) {
            $ssField = $details['SilverstripeField'];
            $moodleField = $details['MoodleField'];
            $type = $details['Type'];
            $returnArray['customfields'][] = [
                'type' => $moodleField,
                'value' => $this->getValueForMoodle($member, $ssField, $type),
            ];
        }
        if ($createPassword) {
            $returnArray['createpassword'] = 1;
        }
        // $returnArray['createpassword'] = 0;

        return $returnArray;
    }

    public function toSilverstripe(array $inputArray)
    {
        $returnArray = [];
        foreach ($this->config()->get('conversion_array') as $details) {
            $ssField = $details['SilverstripeField'];
            $moodleField = $details['MoodleField'];
            $type = $details['Type'];
            $value = $inputArray[$moodleField] ?? null;
            $returnArray[$ssField] = $this->getValueForSilverstripe($ssField, $value);
        }
        $array['customfields'] = [];
        foreach ($this->config()->get('custom_fields') as $details) {
            $ssField = $details['SilverstripeField'];
            $moodleField = $details['MoodleField'];
            $type = $details['Type'];
            foreach ($returnArray[$ssField] = $inputArray['customfields'] as $inputArrayInner) {
                $type = $inputArrayInner['type'] ?? '';
                $value = $inputArrayInner['value'] ?? '';
                if ($type === $moodleField) {
                    $returnArray[$ssField] = $this->getValueForSilverstripe($ssField, $value);
                }
            }
        }

        return $returnArray;
    }

    protected function getValueForMoodle($obj, string $ssField, string $type)
    {
        if ($this->isRelation($ssField)) {
            $val = $this->convertRelationToValue($obj, $ssField, $type);
        } elseif ($obj->hasMethod($ssField)) {
            $val = $obj->{$ssField}();
        } else {
            $val = $obj->{$ssField};
        }
        switch (strtolower($type)) {
            case 'int':
            case 'integer':
                $val = (int) $val;

                break;
            case 'bool':
            case 'boolean':
                $val = $val ? 1 : 0;

                break;
            case 'string':
            default:
                $val = (string) $val;
        }

        return $val;
    }

    protected function getValueForSilverstripe($ssField, $value)
    {
        if ($this->isRelation($ssField)) {
            return $this->convertValueToRelationId($ssField, $value);
        }

        return $value;
    }

    protected function isRelation($ssField): bool
    {
        return (bool) strpos($ssField, '.');
    }

    protected function convertRelationToValue($obj, string $ssField, string $type)
    {
        $methods = explode('.', $ssField);
        $field = array_pop($methods);
        foreach ($methods as $method) {
            $obj = $obj->{$method}();
        }

        return $this->getValueForMoodle($obj, $field, $type);
    }

    protected function convertValueToRelationId(string $ssField, string $value): int
    {
        $methods = explode('.', $ssField);
        $field = array_pop($methods);
        $obj = Injector::inst()->get(Member::class);
        foreach ($methods as $method) {
            $obj = $obj->{$method}();
        }
        $obj = DataObject::get_one($obj->ClassName, [$field => $value]);
        if ($obj) {
            return (int) $obj->ID;
        }

        return 0;
    }

    protected function createUserName(Member $member) : string
    {
        $username = $member->FirstName. ' ' . $member->Surname;
        $username = preg_replace("/[^A-Za-z0-9]/", '_', $username);
        return strtolower(substr($username, 0, 20) . '_' . $member->ID);
    }
}

/*
 * class used to respond with JSON requests
 *
 * createpassword int Optional //True if password should be created and mailed to user. username string //Username policy is defined in Moodle security config.
 * auth string Default to "manual" //Auth plugins include manual, ldap, etc
 * password string Optional //Plain text password consisting of any characters
 * firstname string //The first name(s) of the user
 * lastname string //The family name of the user
 * email string //A valid and unique email address
 * maildisplay int Optional //Email display
 * city string Optional //Home city of the user
 * country string Optional //Home country code of the user, such as AU or CZ
 * timezone string Optional //Timezone code such as Australia/Perth, or 99 for default description string Optional //User profile description, no HTML
 * firstnamephonetic string Optional //The first name(s) phonetically of the user
 * lastnamephonetic string Optional //The family name phonetically of the user
 * middlename string Optional //The middle name of the user
 * alternatename string Optional //The alternate name of the user
 * interests string Optional //User interests (separated by commas)
 * url string Optional //User web page
 * icq string Optional //ICQ number
 * skype string Optional //Skype ID
 * aim string Optional //AIM ID
 * yahoo string Optional //Yahoo ID
 * msn string Optional //MSN ID
 * idnumber string Default to "" //An arbitrary ID code number perhaps from the institution institution string Optional //institution
 * department string Optional //department
 * phone1 string Optional //Phone 1
 * phone2 string Optional //Phone 2
 * address string Optional //Postal address
 * lang string Default to "en" //Language code such as "en", must exist on server
 * calendartype string Default to "gregorian" //Calendar type such as "gregorian", must exist on server
 * theme string Optional //Theme name such as "standard", must exist on server mailformat int Optional //Mail format code is 0 for plain text, 1 for HTML etc customfields Optional //User custom fields (also known as user profil fields) list of (
 * object {
 * type string //The name of the custom field
 * value string //The value of the custom field
 * }
 * )preferences Optional //User preferences
 * list of (
 * object {
 * type string //The name of the preference
 * value string //The value of the preference
 * }
 * )}
 * )
 * response:
 * list of (
 * object {
 * id int //user id
 * username string //user name
 * }
 *
 */
