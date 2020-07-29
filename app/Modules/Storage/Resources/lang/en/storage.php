<?php
return [
	'module name' => 'Storage',
	'storage' => 'Storage',
	//'CONFIGURATION' => 'Cron Configuration',
	//'RUN' => 'Run',
	//'ARTICLES' => 'Articles',
	//'TYPES' => 'Categories',
	//'TEMPLATES' => 'Templates',
	'resource' => 'Resource',

	// Columns
	'id' => 'ID',
	'name' => 'Name',
	'path' => 'Path',
	'quota space' => 'Space Quota',
	'quota file' => 'File Quota',
	'active' => 'Active',
	'created' => 'Created',
	'removed' => 'Removed',
	'directories' => 'Directories',
	'quota' => 'Quota',
	'owner' => 'Owner',
	'group' => 'Group',
	'import hostname' => 'Import hostname',
	'get quota type' => '"get quota" message',
	'create type' => '"make directory" message',
	'message queue' => 'Message Queue',

	// Misc.
	'active' => 'Active',
	'inactive' => 'Inactive',
	'NO_DATE_SET' => '(no date set)',
	//'NONE' => '(none)',
	//'NEVER' => '(never)',
	'SET_THIS_TO' => 'Set this to %s',
	'STATE_UNPUBLISH' => 'unpublish',
	'STATE_PUBLISH' => 'publish',
	'SELECT' => 'Select...',
	'DEACTIVATE' => 'Deactivate',
	'HISTORY_EDITED' => '%s edited the page @ %s',
	'HISTORY' => 'History',

	// Errors
	'ERROR_NO_ITEMS_SELECTED' => 'No entry selected',
	'ERROR_SELECT_ITEMS' => 'Select an entry to %s',
	'ERROR_MISSING_TITLE' => 'Entry must have a title',

	// Messages
	'ITEM_SAVED' => 'Item Successfully Saved',
	'ITEMS_DELETED' => '%s Item(s) Successfully Removed',
	'ITEMS_PUBLISHED' => '%s Item(s) successfully published',
	'ITEMS_UNPUBLISHED' => '%s Item(s) successfully unpublished',
	'ITEMS_DEACTIVATED' => '%s Item(s) successfully deactivated',
	'CONFIRM_DELETE' => 'Are you sure you want to delete these items?',

	// Fields
	'role name' => 'Role Name',
	'list name' => 'List Name',
	'resource type' => 'Resource',
	'product type' => 'Product',
	'FIELD_PARENT' => 'Parent Resource',
	'FIELD_CREATED' => 'Created',
	'FIELD_ID' => 'ID',
	'FIELD_REMOVED' => 'Removed',
	'FIELD_DEFAULTQUOTASPACE' => 'Default Quota Space',
	'FIELD_DEFAULTQUOTAFILE' => 'Default Quota File',
	'FILESIZE' => 'Size in bytes. Use size abbreviations (PB, TB, KB, MB, B). Values with no abbreviation will be taken as bytes. Ex: 100000 = 100000 B',
	'FIELD_IMPORTHOSTNAME' => 'Import Host Name',
	'FIELD_IMPORT' => 'Import',
	'FIELD_AUTOUSERDIR' => 'Auto User Dir',
	'FIELD_GETQUOTATYPEID' => 'Get Quota Type ID',
	'FIELD_CREATETYPEID' => 'Create Type ID',

	// Config
	'CONFIG_WHITELIST_LABEL' => 'IP Whitelist',
	'CONFIG_WHITELIST_DESC' => 'A comma-separated list of white-listed IP addresses',

	'my quotas' => 'Quotas',
];
