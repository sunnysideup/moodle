<?php

namespace Sunnysideup\Moodle\Model;

use Symfony\Component\Console\Input\InputInterface;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use SilverStripe\Dev\BuildTask;

use SilverStripe\ORM\DB;

use Sunnysideup\Moodle\Model\MoodleLog;

class MoodleLogErrorList extends BuildTask
{
    protected string $title = 'Check for Moodle Errors and list them (use ?all=1 to show all)';

    protected static string $description = 'Run through all the errors and summarise per member in reverse chronological order.';

    /**
     * @config
     */
    private static $is_enabled = true;

    protected $byEmail = [];

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $totalCount = MoodleLog::get()->count();
        $logs = MoodleLog::get()->filter(['IsSuccess' => false]);
        $errorCount = $logs->count();
        echo '<h2>Error Percentage (' . $errorCount . ' / ' . $totalCount . ') = ' . round(($errorCount / $totalCount) * 100, 2) . '%</h2>';
        foreach ($logs as $log) {
            $log->write();
            $successLater = MoodleLog::get()
                ->filter(
                    [
                        'IsSuccess' => true,
                        'MemberID' => $log->MemberID,
                        'ID:GreaterThan' => $log->ID,
                    ]
                )->exists();
            if ((bool) $successLater === false || ! empty($_GET['all'])) {
                $email = $log->Member()->Email;
                if (! isset($this->byEmail[$email])) {
                    $this->byEmail[$email] = [];
                }

                $this->byEmail[$email][] = [
                    'ErrorMessage' => $log->ErrorMessage,
                    'Created' => $log->Created,
                    'Link' => $log->CMSEditLink(),
                ];
            }
        }

        foreach ($this->byEmail as $email => $items) {
            $output->writeln('<hr />');
            DB::alteration_message('<strong>' . $email . '</strong>');
            foreach ($items as $item) {
                DB::alteration_message('...  ... <a href="' . $item['Link'] . '">' . $item['Created'] . ': ' . $item['ErrorMessage'] . '</a>');
            }
        }

        return Command::SUCCESS;
    }
}
