<?php

namespace Sunnysideup\Moodle\Model;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;

use SilverStripe\ORM\DB;

use Sunnysideup\Moodle\Model\MoodleLog;

class MoodleLogErrorList extends BuildTask
{
    protected $title = 'Check for Moodle Errors and list them (use ?all=1 to show all)';

    protected $description = 'Run through all the errors and summarise per member in reverse chronological order.';

    protected $enabled = true;

    protected $byEmail = [];

    public function run($request)
    {
        $totalCount = MoodleLog::get()->count();
        $logs = MoodleLog::get()->filter(['IsSuccess' => false]);
        $errorCount = $logs->count();
        echo '<h2>Error Percentage ('.$errorCount.' / '.$totalCount.') = '.round(($errorCount/$totalCount) * 100, 2).'%</h2>';
        foreach($logs as $log) {
            $log->write();
            $successLater = MoodleLog::get()
                ->filter(
                    [
                        'IsSuccess' => true,
                        'MemberID' => $log->MemberID,
                        'ID:GreaterThan' => $log->ID,
                    ]
                )->exists();
            if($successLater === false || $_GET['all']) {
                $email = $log->Member()->Email;
                if(! isset($this->byEmail[$email])) {
                    $this->byEmail[$email] = [];
                }
                $this->byEmail[$email][] = [
                    'ErrorMessage' => $log->ErrorMessage,
                    'Created' => $log->Created,
                    'Link' => $log->CMSEditLink(),
                ];
            }
        }
        foreach($this->byEmail as $email => $items) {
            echo '<hr />';
            DB::alteration_message('<strong>'.$email.'</strong>');
            foreach($items as $item) {
                DB::alteration_message('...  ... <a href="'.$item['Link'].'">'.$item['Created'].': '.$item['ErrorMessage'].'</a>');
            }
        }
    }
}
