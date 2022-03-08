<?php
return [
	'module name' => 'Queue Manager',
	'module sections' => 'Module sections',
	'queues' => 'Queues',
	'queue' => 'Queue',
	'purchases and loans' => 'Purchases & Loans',
	'types' => 'Types',
	'type' => 'Type',
	'state' => 'State',
	//'edit' => 'Edit',
	//'create' => 'Create',
	'id' => 'ID',
	'name' => 'Name',
	'name error' => 'The field "Name" is required.',
	'all types' => '- All Types -',
	'enabled' => 'Enabled',
	'disabled' => 'Disabled',
	'trashed' => 'Trashed',
	'created' => 'Created',
	'removed' => 'Removed',
	'default walltime' => 'Default Walltime',
	'max walltime' => 'Max Walltime',
	'resource' => 'Resource',
	'subresource' => 'Node Type',
	'set state to' => 'Set this to :state',
	'node memory minimum' => 'Min memory',
	'node memory maximum' => 'Max memory',
	'last seen' => 'Last seen',
	'all queue classes' => '- All Classes -',
	'class' => 'Class',
	'system' => 'System',
	'system queues' => 'System queues',
	'owner' => 'Owner',
	'owner queues' => 'Owner queues',
	'all resources' => '- All Resources -',
	'scheduler' => 'Scheduler',
	'schedulers' => 'Schedulers',
	'scheduler policies' => 'Scheduler Policies',
	'all batch systems' => '- Select Batch system -',
	'batch system' => 'Batch system',
	'batch system warning' => 'Changing this value to anything other than PBS can have drastic effects for <code>qcontrol</code>.',
	'all scheduler policies' => '- Select Scheduler Policy -',
	'scheduler policy' => 'Scheduler Policy',
	'hostname' => 'Hostname',
	'default max walltime' => 'Max Walltime',
	'group' => 'Group',
	'search for group' => 'Search for group...',
	'jobs' => 'Jobs',
	'max jobs queued' => 'Max Jobs Queued',
	'max jobs queued per user' => 'Max Jobs Queued per User',
	'max jobs run' => 'Max Jobs Running',
	'max jobs run per user' => 'Max Jobs Running per User',
	'max job cores' => 'Max Cores per Job',
	'walltime' => 'Walltime',
	'node cores default' => 'Default Node Cores',
	'node cores min' => 'Minimum Node Cores',
	'node cores max' => 'Max Node Cores',
	'node mem min' => 'Minimum Node Memory',
	'node mem max' => 'Max Node Memory',
	'scheduling' => 'Scheduling',
	'stopped' => 'Stopped',
	'started' => 'Started',
	'select group' => '(select group)',
	'messages' => [
		'items enabled' => 'Queue(s) enabled.',
		'items disabled' => 'Queue(s) disabled.',
		'items stopped' => 'Scheduling stopped on selected queues.',
		'items started' => 'Scheduling started on selected queues.',
	],
	'reservation' => 'Dedicated Reservation',
	'reservation desc' => 'Allow dedicated reservations?',
	'queue has dedicated reservation' => 'Queue has dedicated reservation.',
	'queue is running' => 'Queue is running.',
	'queue is stopped' => 'Queue is stopped or disabled.',
	'queue has not active resources' => 'Queue has no active resources. Remove queue or sell/loan nodes or service units.',
	'max ijob factor' => 'Max Jobs per Iteration Factor',
	'max ijob user factor' => 'Max Jobs per User Iteration Factor',
	'cluster' => 'Subcluster(s)',
	'acl users enabled' => 'User ACL Enabled',
	'acl users enabled desc' => 'User ACL Enabled',
	'acl groups' => 'Group ACL',
	'acl groups desc' => 'Comma separated list of ACL groups',
	'priority' => 'Priority',
	'submission state' => 'Submission to the queue',
	'nodes' => 'Nodes',
	'cores' => 'Cores',
	'service units' => 'Service Units',
	'total' => 'Total',
	'amount' => 'Amount',
	'loans' => 'Loans',
	'access' => 'Access',
	'sell' => 'Sell',
	'loan' => 'Loan',
	'seller' => 'Seller',
	'lender' => 'Lender',
	'org owned' => '(ITaP-Owned)',
	'standby' => 'Standby',
	'owner' => 'Owner',
	'work' => 'Workq',
	'debug' => 'Debug',
	'end of life' => 'end of cluster life',
	'nodes' => 'Nodes',
	'cores' => 'Cores',
	'start' => 'Start',
	'stop' => 'Stop',
	'end' => 'End',
	'free' => 'Free',
	'free desc' => 'Can be reserved for free?',
	'comment' => 'Comment',
	'error' => [
		'invalid name' => 'Please provide a valid name.',
		'invalid hostname' => 'Please provide a valid hostname.',
		'invalid scheduler' => 'Please select a scheduler.',
		'invalid subresource' => 'Please select a node type.',
		'start cannot be after stop' => 'Field `start` cannot be after or equal to stop time',
		'corecount cannot be modified' => 'Core count cannot be modified on entries already in affect',
		'invalid corecount' => 'Invalid `corecount` value',
		'queue is empty' => 'Have not been sold anything and never will have anything',
		'queue has not started' => 'Have not been sold anything before this would start',
		'queue already exists' => 'A queue with the provided name and resource already exists',
		'start cannot be after end' => 'Field `start` cannot be after or equal to stop time',
		'failed to find counter' => 'Failed to retrieve counter entry',
		'failed to update counter' => 'Failed to update counter entry for #:id',
		'invalid queue' => 'Unknown or invalid queue',
		'entry already exists for hostname' => 'Entry already exists for `:hostname`',
	],
	'number cores' => ':num cores',
	'number memory' => ':num memory',
	'select queue' => '(Select Queue)',
	'loan to' => 'Loan to',
	'sell to' => 'Sell to',
	'action' => 'Action',
	'source' => 'Source',
	'start scheduling' => 'Start scheduling',
	'stop scheduling' => 'Stop scheduling',
	'start all scheduling' => 'Start all scheduling',
	'stop all scheduling' => 'Stop all scheduling',
	'options' => 'Options',
	'all states' => '- All States -',
	'edit loan' => 'Edit loan',
	'edit size' => 'Edit purchase',
	'new hardware' => 'New hardware',
	'cores per node' => ':cores per node',
	'saving' => 'Saving...',
	'entry marked as trashed' => 'This entry is marked as trashed.',
	'list of queues' => 'Below is a list of all queues',
	'confirm delete queue' => 'Are you sure you want to delete this queue?',
	'stats' => 'Stats',
	'member' => 'Member',
	'pending' => 'Pending',
	'status' => 'Status',
];