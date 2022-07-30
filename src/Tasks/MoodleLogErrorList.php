<?php

namespace Sunnysideup\Moodle\Model;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;

use SilverStripe\ORM\DB;

use Sunnysideup\Moodle\Model\MoodleLog;

class MoodleLogErrorList extends BuildTask
{
    protected $title = 'Check for Moodle Errors and list them';

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
            $email = $log->Member()->Email;
            if(! isset($this->byEmail[$email])) {
                $this->byEmail[$email] = [];
            }
            $this->byEmail[$email][] = [
                'ErrorMessage' => $log->ErrorMessage,
                'Created' => $log->Created,
            ];
        }
        foreach($this->byEmail as $email => $items) {
            DB::alteration_message('<strong>'.$email.'</strong>');
            foreach($items as $item) {
                DB::alteration_message('...  ... '.$item['Created'].': '.$item['ErrorMessage']);
            }
        }
    }
}
