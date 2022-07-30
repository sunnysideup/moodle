<?php

namespace Sunnysideup\Moodle\Admin;

use Sunnysideup\Moodle\Model\MoodleLog;

use SilverStripe\Admin\ModelAdmin;



class MoodleModelAdmin extends ModelAdmin
{
    /**
     * Creates a tab on the cms to manage all the categories.
     *
     * @var array
     */
    private static $managed_models = [
        MoodleLog::class,
    ];

    private static $menu_icon_class = 'font-icon-book';

    private static $url_segment = 'moodle'; // your URL name admin/articles

    private static $menu_title = 'Moodle '; //Name on the CMS

}
