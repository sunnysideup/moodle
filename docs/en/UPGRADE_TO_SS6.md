# Upgrade to Silverstripe CMS 6

## Framework Requirements

⚠️ **BREAKING CHANGE**: Minimum framework version is now `^6.0`. Update your `composer.json`:

```json
"silverstripe/framework": "^6.0"
```

## Database Query API Changes

⚠️ **BREAKING CHANGE**: All legacy `DataObject::get_one()` calls replaced with modern ORM syntax.

Replace static `get_one()` calls with chained ORM methods:

```php
// Before
$obj = DataObject::get_one($className, [$field => $value]);
$group = DataObject::get_one(Group::class, ['MoodleUid' => $courseId]);

// After
$obj = $className::get()->setUseCache(true)->filter([$field => $value])->first();
$group = Group::get()->setUseCache(true)->filter(['MoodleUid' => $courseId])->first();
```

**🚨 CRITICAL REVIEW REQUIRED**: Dynamic class name resolution in `UserToMoodleUserConversionApi::getFieldValueAsInt()` at line 40:

```php
$obj = $obj->ClassName::get()->setUseCache(true)->filter([$field => $value])->first();
```

This uses `$obj->ClassName` as a dynamic class reference. Verify this works correctly with your PHP version (8.2+) and test thoroughly with all DataObject types that flow through this method.

## Namespace Changes

⚠️ **BREAKING CHANGE**: `ArrayList` moved to new namespace.

```php
// Before
use SilverStripe\ORM\ArrayList;

// After
use SilverStripe\Model\List\ArrayList;
```

## PHP 8.2+ Type Declarations

Typed class constants now use modern syntax:

```php
// Before
private const MOODLE_PARENT_GROUP_CODE = 'MOODLES';

// After
private const string MOODLE_PARENT_GROUP_CODE = 'MOODLES';
```

## Override Attribute

Add `#[Override]` attribute to all overridden methods for better type safety:

- `UpdateUser::runAction()`
- `UpdateUser::createData()`
- `UpdateUser::validateParams()`
- `MoodleLog::canEdit()`
- `MoodleLog::canDelete()`
- `MoodleLog::onBeforeWrite()`
- `MoodleLog::onAfterWrite()`
- `MoodleLog::getCMSFields()`
- `MoodleLog::CMSEditLink()`

Import at the top of affected classes:

```php
use Override;
```

## CLI Task Migration

⚠️ **BREAKING CHANGE**: `MoodleLogErrorList` migrated from BuildTask to Symfony Console Command.

Replace `BuildTask::run()` pattern with `execute()`:

```php
// Before
public function run($request) {
    $all = !empty($_GET['all']);
}

// After
protected function execute(InputInterface $input, PolyOutput $output): int {
    $all = $input->getOption('all');
    return Command::SUCCESS;
}
```

Key changes:
- Add `protected static string $commandName = 'moodle-log-error-list';`
- Change `$title` to `protected string`
- Change `$description` to `protected static string`
- Replace `echo` with `$output->writeForHtml()`
- Replace `DB::alteration_message()` with `$output->writeForHtml()`
- Implement `getOptions()` to define command options
- Return `Command::SUCCESS`

Required imports:

```php
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
```

**🚨 CRITICAL REVIEW REQUIRED**: CLI task migration is incomplete. The class still extends `BuildTask` instead of a Symfony Console Command base class. You must either:

1. Change the base class to the appropriate Silverstripe 6 console command class, OR
2. Remove the `BuildTask` parent entirely if using pure Symfony commands

Test the command invocation thoroughly:

```bash
# Old way (may no longer work)
sake dev/tasks/MoodleLogErrorList

# New way
vendor/bin/sake moodle-log-error-list --all
```

## Removed Code

- Removed unused `use SilverStripe\ORM\DataObject;` imports (replaced by direct class references)
- Removed `parent::onBeforeWrite()` call in `GroupExtension::onBeforeWrite()` (verify this is intentional for your extension logic)
- Removed `use SilverStripe\ORM\DB;` from `MoodleLogErrorList`

## String Type Casting

Added explicit string cast for `strpos()` call to satisfy strict types:

```php
if (!strpos((string) $this->getOwner()->Title, self::MOODLE_NAME_POST_FIX))
```

## Testing Checklist

1. Run full test suite after upgrade
2. Test all Moodle user sync operations (create/update)
3. Verify group enrollment and course mapping
4. Test `moodle-log-error-list` command with and without `--all` flag
5. Check all database queries return expected results
6. Verify dynamic class name resolution in conversion API works with all DataObject types
7. Confirm BuildTask to Console Command migration is complete and functional
