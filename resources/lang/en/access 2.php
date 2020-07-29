<?php

return [
	// Actions
	'permissions' => 'Permissions',
	'permissions description' => 'Default permissions used for all content in this module.',
	'admin' => 'Super Admin',
	'admin description' => 'Allows users in the group to perform any action regardless of the settings.',
	'module settings' => 'Module Settings',
	'action' => [
		'admin' => 'Configure',
		'admin description' => 'Allows users in the group to edit the options of this extension.',
		'manage' => 'Access Administration Interface',
		'manage description' => 'Allows users in the group to access the administration interface for this extension.',
		'create' => 'Create',
		'create description' => 'Allows users in the group to create any content in this extension.',
		'delete' => 'Delete',
		'delete description' => 'Allows users in the group to delete any content in this extension.',
		'edit' => 'Edit',
		'edit description' => 'Allows users in the group to edit any content in this extension.',
		'edit state' => 'Edit State',
		'edit state description' => 'Allows users in the group to change the state of any content in this extension.',
		'edit own' => 'Edit Own',
		'edit own description' => 'Allows users in the group to edit any content they submitted in this extension.',
	],
	'rules' => [
		'ACTION' => 'Action',
		'ALLOWED' => 'Allowed',
		'ALLOWED_ADMIN' => 'Allowed (Super Admin)',
		'CALCULATED_SETTING' => 'Calculated Setting <sup>2</sup>',
		'CONFLICT' => 'Conflict',
		'DENIED' => 'Denied',
		'GROUP' => ':group',
		'GROUPS' => 'Groups',
		'INHERIT' => 'Inherit',
		'INHERITED' => 'Inherited',
		'NOT_ALLOWED' => 'Not Allowed',
		'NOT_ALLOWED_ADMIN_CONFLICT' => 'Conflict',
		'NOT_ALLOWED_LOCKED' => 'Not Allowed (Locked)',
		'NOT_SET' => 'Not Set',
		'SELECT_ALLOW_DENY_GROUP' => 'Allow or deny :action for users in the :group group',
		'SELECT_SETTING' => 'Select New Setting <sup>1</sup>',
		'SETTING_NOTES' => '1. If you change the setting, it will apply to this and all child groups, components and content. Note that <em>Denied</em> will overrule any inherited setting, and also the setting in any child group, component or content. In the case of a setting conflict, <em>Deny</em> will take precedence. <em>Not Set</em> is equivalent to <em>Denied</em> but can be changed in child groups, components and content.<br />2. If you select a new setting, click <em>Save</em> to refresh the calculated settings.',
		'SETTING_NOTES_ITEM' => '1. If you change the setting, it will apply to this item. Note that:<br /><em>Inherited</em> means that the permissions from global configuration, parent group and category will be used.<br /><em>Denied</em> means that no matter what the global configuration, parent group or category settings are, the group being edited cannot take this action on this item.<br /><em>Allowed</em> means that the group being edited will be able to take this action for this item (but if this is in conflict with the global configuration, parent group or category it will have no impact; a conflict will be indicated by <em>Not Allowed (Locked)</em> under Calculated Settings).<br />2. If you select a new setting, click <em>Save</em> to refresh the calculated settings.',
		'SETTINGS_DESC' => 'Manage the permission settings for the user groups below. See notes at the bottom.',
	],
];
