<?php
return [
	'module name' => 'Scheduled Tasks',
	'RUN' => 'Run',
	'JOBS' => 'Jobs',
	'PLUGINS' => 'Plugins',

	// Columns
	'id' => 'ID',
	'description' => 'Description',
	'command' => 'Command',
	'command desc' => 'Select a command to schedule',
	'state' => 'State',
	'starts' => 'Starts',
	'ends' => 'Ends',
	'active' => 'Active',
	'last run' => 'Last Run',
	'next run' => 'Next Run',
	'recurrence' => 'Recurrence',
	'overlap' => 'Overlap?',
	'dont overlap desc' => 'Do not allow new processes to be spawned if old ones haven\'t finished yet.',
	'overlap desc' => 'Allow new processes to be spawned even if old ones haven\'t finished yet.',
	'parameters' => 'Parameters',
	'parameters desc' => 'Command parameters to be run',

	// Misc.
	'inactive' => 'Inactive',
	'no date set' => '(no date set)',
	'set this to' => 'Set this to %s',
	'SELECT' => 'Select...',
	'DEACTIVATE' => 'Deactivate',

	// Errors
	//'ERROR_NO_ITEMS_SELECTED' => 'No entry selected',
	//'ERROR_SELECT_ITEMS' => 'Select an entry to %s',
	//'ERROR_MISSING_TITLE' => 'Entry must have a title',

	// Messages
	//'ITEM_SAVED' => 'Item Successfully Saved',
	//'ITEMS_DELETED' => '%s Item(s) Successfully Removed',
	//'ITEMS_PUBLISHED' => '%s Item(s) successfully published',
	//'ITEMS_UNPUBLISHED' => '%s Item(s) successfully unpublished',
	//'ITEMS_DEACTIVATED' => '%s Item(s) successfully deactivated',
	//'CONFIRM_DELETE' => 'Are you sure you want to delete these items?',

	// Fields
	'recurrence' => 'Recurrence',
	'common' => 'Frequency',
	'minute' => 'Minute',
	'hour' => 'Hour',
	'month' => 'Month',
	'day of month' => 'Day of month',
	'day of week' => 'Day of week',
	'option' => [
		'select' => 'Select...',
		'custom' => '[ Custom ]',
		'once a year' => 'Run once a year, midnight, Jan. 1st',
		'once a month' => 'Run once a month, midnight, first of month',
		'once a week' => 'Run once a week, midnight on Sunday',
		'once a day' => 'Run once a day, midnight',
		'once an hour' => 'Run once an hour, beginning of hour',
		'every' => 'Every',
		'every other' => 'Every Other',
		'every four' => 'Every 4',
		'every five' => 'Every 5',
		'every ten' => 'Every 10',
		'every fifteen' => 'Every 15',
		'every thirty' => 'Every 30',
		'every three' => 'Every Three (quarterly)',
		'every six' => 'Every Six',
		'midnight' => '0 = 12AM/Midnight',
	],
	//'FIELD_COMMON_OPT_SELECT' => 'Select...',
	//'FIELD_COMMON_OPT_CUSTOM' => '[ Custom ]',
	//'FIELD_COMMON_OPT_ONCE_A_YEAR' => 'Run once a year, midnight, Jan. 1st',
	//'FIELD_COMMON_OPT_ONCE_A_MONTH' => 'Run once a month, midnight, first of month',
	//'FIELD_COMMON_OPT_ONCE_A_WEEK' => 'Run once a week, midnight on Sunday',
	//'FIELD_COMMON_OPT_ONCE_A_DAY' => 'Run once a day, midnight',
	//'FIELD_COMMON_OPT_ONCE_AN_HOUR' => 'Run once an hour, beginning of hour',
	'FIELD_CREATED' => 'Created',
	'FIELD_CREATOR' => 'Creator',
	'FIELD_ID' => 'ID',
	'FIELD_MODIFIED' => 'Modified',
	'FIELD_MODIFIER' => 'Modifier',
	'FIELD_STATE' => 'State',
	'FIELD_START_RUNNING' => 'Start running',
	'FIELD_STOP_RUNNING' => 'Stop running',
	//'FIELD_OPT_CUSTOM' => 'Custom',
	//'FIELD_OPT_EVERY' => 'Every',
	//'FIELD_OPT_EVERY_OTHER' => 'Every Other',
	//'FIELD_OPT_EVERY_FOUR' => 'Every Four',
	//'FIELD_OPT_EVERY_FIVE' => 'Every 5',
	//'FIELD_OPT_EVERY_TEN' => 'Every 10',
	//'FIELD_OPT_EVERY_FIFTEEN' => 'Every 15',
	//'FIELD_OPT_EVERY_THIRTY' => 'Every 30',
	//'FIELD_OPT_EVERY_THREE' => 'Every Three (quarterly)',
	//'FIELD_OPT_EVERY_SIX' => 'Every Six',
	//'FIELD_OPT_MIDNIGHT' => '0 = 12AM/Midnight',
];
