<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Users\RcacLdap;

//use App\Modules\Users\Events\UserSyncing;
use Illuminate\Support\Facades\Log;
use App\Modules\Users\Events\UserSearching;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Users\Models\User;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Halcyon\Utility\Str;

/**
 * User listener for RCAC Ldap
 */
class RcacLdap
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserSearching::class, self::class . '@handleUserSearching');
		$events->listen(UserBeforeDisplay::class, self::class . '@handleUserBeforeDisplay');
		$events->listen(ResourceMemberStatus::class, self::class . '@handleResourceMemberStatus');
	}

	/**
	 * Search for users
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleUserSearching(UserSearching $event)
	{
		if (!app()->has('ldap'))
		{
			return;
		}

		$config = config('ldap.rcac', []);

		if (empty($config))
		{
			return;
		}

		$usernames = array();
		foreach ($event->results as $user)
		{
			$usernames[] = $user->username;
		}

		// We already found a macth
		if (in_array($event->search, $usernames))
		{
			return;
		}

		try
		{
			$ldap = app('ldap')
				->addProvider($config, 'rcac')
				->connect('rcac');

			// Performing a query.
			$results = $ldap->search()
				->where('uid', '=', $event->search)
				->select(['cn', 'uid'])
				->get();

			foreach ($results as $result)
			{
				/*if ($event->results->count() >= $event->results->total())
				{
					break;
				}*/

				// We have a local record for this user
				if (in_array($result['uid'][0], $usernames))
				{
					continue;
				}

				$user = new User;
				$user->name = Str::properCaseNoun($result['cn'][0]);
				$user->username = $result['uid'][0];
				$user->email = $user->username . '@purdue.edu';

				$usernames[] = $user->username;

				$event->results->push($user);
			}

			// Update pagination information
			$data = $event->results->toArray();

			$query = parse_url($data['first_page_url'], PHP_URL_QUERY);
			parse_str($query, $output);

			$itemsTransformedAndPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
				$event->results->getCollection(),
				count($data['data']),
				$event->results->perPage(),
				$event->results->currentPage(),
				[
					'path' => \Request::url(),
					'query' => $output
				]
			);

			$event->results = $itemsTransformedAndPaginated;
		}
		catch (\Exception $e)
		{
			Log::error($e->getMessage());
		}
	}

	/**
	 * Display user profile info
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleUserBeforeDisplay(UserBeforeDisplay $event)
	{
		if (!app()->has('ldap'))
		{
			return;
		}

		$config = config('ldap.rcac', []);

		if (empty($config))
		{
			return;
		}

		$user = $event->getUser();

		try
		{
			$ldap = app('ldap')
				->addProvider($config, 'rcac')
				->connect('rcac');

			// Performing a query.
			$results = $ldap->search()
				->where('uid', '=', $event->username)
				->select(['loginShell'])
				->get();

			foreach ($results as $data)
			{
				if (isset($data['loginShell']))
				{
					$user->loginshell = $data['loginShell'][0];
				}
			}
		}
		catch (\Exception $e)
		{
			$user->loginshell = false;
			//$user->setError($e->getMessage());
			Log::error($e->getMessage());
		}

		$event->setUser($user);
	}

	/*public function handle($event)
	{
		$ldap_users    = array();
		$system_users  = array();
		$scholar_users = $event->users;

		if (!app()->has('ldap'))
		{
			return;
		}

		$config = config('ldap.rcac', []);

		if (empty($config))
		{
			return;
		}

		try
		{
			$ldap = app('ldap')
				->addProvider($config, 'rcac')
				->connect('rcac');

			// Performing a query.
			$results = $ldap->search()
				->where('host', '=', 'scholar.rcac.purdue.edu')
				->select(['uid'])
				->get();

			foreach ($results as $row)
			{
				// Try to subtract staff users. Subtact anyone in xenon.
				$rows = $ldap->search()
					->where(
						['uid', '=', $row['uid'][0]],
						['host', '=', 'xenon.rcac.purdue.edu']
					)
					->select(['uid'])
					->get();

				if (empty($rows))
				{
					$ldap_users[$row['uid'][0]] = $row['uid'][0];
				}
				else
				{
					$system_users[$row['uid'][0]] = $row['uid'][0];
				}

				$rows = $ldap->search()
					->where(
						['uid', '=', $row['uid'][0]]
					)
					->select(['classification'])
					->get();

				if (!empty($rows))
				{
					if (isset($rows[0]['classification'][0])
					 && ($rows[0]['classification'][0] == 'System Account' || $rows[0]['classification'][0] == 'Software Account'))
					{
						$system_users[$row['uid'][0]] = $row['uid'][0];
					}
				}
			}
		}
		catch (\Exception $e)
		{
			
		}

		//$event->setUsers($ldap_users);
		//$event->setSystemUsers($system_users);

		$create_users = array_diff($scholar_users, $ldap_users);
		$create_users = array_diff($create_users, $system_users);
		$remove_users = array_diff($ldap_users, $scholar_users);
		$remove_users = array_diff($remove_users, $system_users);

		$event->setCreateUsers($create_users);
		$event->setRemoveUsers($remove_users);
	}*/

	public function handleResourceMemberStatus(ResourceMemberStatus $event)
	{
		if (!app()->has('ldap'))
		{
			return;
		}

		$config = config('ldap.rcac', []);

		if (empty($config))
		{
			return;
		}

		$event->user->pilogin = '';
		$event->user->loginshell = '/bin/bash';

		if ($event->resource->rolename != 'peregrn1')
		{
			$event->user->primarygroup = 'student';
		}
		else
		{
			// DO NOT use "Calumet" even though this is how it shows up in our LDAP
			// "Calumet" is a different group in ACMaint, we want "calumet", gid 5882
			$event->user->primarygroup = 'calumet';
		}

		try
		{
			$ldap = app('ldap')
				->addProvider($config, 'rcac')
				->connect('rcac');

			// Performing a query.
			$results = $ldap->search()
				->where('uid', '=', $event->user->username)
				->select(['loginShell', 'authorizedBy', 'gidNumber'])
				->get();

			if (!empty($results))
			{
				$this->loginshell = $results[0]['loginshell'][0];

				if (isset($results[0]['authorizedby']))
				{
					$event->user->pilogin = $results[0]['authorizedby'][0];
				}

				$gid = $data[0]['gidnumber'][0];

				// Resolve group name
				$data = array();
				$data = $ldap_group->search()
					->where('gidNumber', '=', $gid)
					->select(['cn', 'gidNumber'])
					->get();

				if (!empty($data))
				{
					$event->user->primarygroup = $data[0]['cn'][0];
				}
			}
		}
		catch (\Exception $e)
		{
		}
	}
}
