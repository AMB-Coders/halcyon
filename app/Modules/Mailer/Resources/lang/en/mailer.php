<?php
return [
	'module name' => 'Mail',
	'messages' => 'Messages',
	'message' => 'Message',
	'configuration' => 'Mail Configuration',
	'id' => 'ID',
	'name' => 'Name',
	'name hint' => 'A short label for the template. This is used to differentiate templates that may have the same subject line. If none is provided, the subject line will be used.',
	'subject' => 'Subject',
	'body' => 'Body',
	'body formatting' => 'Text is formatted in <a data-toggle="modal" data-target="#markdown" href="#markdown">MarkDown</a>.',
	'invalid' => [
		'subject' => 'Please provide a valid subject',
		'body' => 'Please provide a valid body',
		'recipient' => 'Invalid value ":id"',
		'user list' => 'No valid recipients found.',
	],
	'created' => 'Created',
	'modified' => 'Last Modified',
	'sent at' => 'Sent',
	'send to hint' => 'Search for users or enter email addresses, separated by commas.',
	'input text' => 'Input text',
	'output text' => 'Output text',
	'send' => 'Send',
	'total sent' => 'Total sent',
	'from' => 'From',
	'to' => 'To',
	'to individuals' => 'To individuals',
	'to group' => 'To everyone in group',
	'to role' => 'To everyone with role',
	'cc' => 'CC',
	'bcc' => 'BCC',
	'send to' => 'Send to',
	'sent by' => 'Sent by',
	'templates' => 'Templates',
	'template' => 'Template',
	'use template' => 'Use this template',
	'sent' => 'Sent messages',
	'options' => 'Options',
	'alert level' => 'Alert level',
	'alert level description' => 'Selecting an alert level will alter the style of the email sent, typically with a banner, to distinguish it more clearly from other system emails.',
	'alert' => [
		'info' => 'Info',
		'warning' => 'Warning',
		'danger' => 'Danger',
	],
	'from email' => 'From email',
	'from name' => 'From name',
	'from me' => 'Me',
	'variable' => 'Variable',
	'variables' => 'Variables',
	'example result' => 'Example Output',
	'variable replacement' => 'Variable placeholders can be used to dynamically inject content into emails. This is particularly useful when sending the same email to multiple recipients.',
	'sent message to' => 'Sent message to :count people.',
	'error' => [
		'account not found' => 'Could not find account for user ID :id',
	],
	'group confirmation' => 'Selecting a group will email all users in that gorup. This could be a significant number of people so please be certain.',
	'role confirmation' => 'Selecting a role will email all users assigned that role. This could be a significant number of people so please be certain.',
	'copy' => 'Copy',
];
