<?php

namespace Sunnysideup\Moodle\Users;

use Sunnysideup\Moodle\MoodleAction;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;

/**
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
class UpdateUser Extends CreateUser {

    public function runAction(DataList $members)
    {

        return parent::runAction('core_user_update_users', $params);
    }


}
