<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Users\PedLdap;

//use App\Modules\Users\Events\UserSyncing;
use App\Modules\Users\Events\UserSearching;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Users\Models\User;
//use Illuminate\Support\Facades\Log;
use App\Modules\History\Traits\Loggable;
use App\Halcyon\Utility\Str;

/**
 * User listener for Purdue Ldap
 */
class PedLdap
{
	use Loggable;

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
	}

	/**
	 * Get LDAP config
	 *
	 * @return  array
	 */
	private function config()
	{
		if (!app()->has('ldap'))
		{
			return array();
		}

		return config('ldap.ped', []);
	}

	/**
	 * Establish LDAP connection
	 *
	 * @param   array  $config
	 * @return  object
	 */
	private function connect($config)
	{
		return app('ldap')
				->addProvider($config, 'ped')
				->connect('ped');
	}

	/**
	 * Search for users
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleUserSearching(UserSearching $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$usernames = array();
		foreach ($event->results as $user)
		{
			$usernames[] = $user->username;
		}

		try
		{
			$ldap = $this->connect($config);

			$status = 404;

			// We already found a match, so kip this lookup
			if (!in_array($event->search, $usernames))
			{
				// Look for a currently active username in I2A2 matching the request.
				$results = $ldap->search()
					->where('uid', '=', $event->search)
					->select(['cn', 'uid', 'title', 'purdueEduCampus'])
					->get();

				foreach ($results as $result)
				{
					if ($event->results->count() >= $event->results->total())
					{
						break;
					}

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
			}

			// Look for all currently active users in I2A2 with a real name matching the request.
			$results = $ldap->search()
				->orWhere('cn', '=', $event->search)
				->orWhere('cn', 'ends_with', ' ' . $event->search)
				->select(['cn', 'uid', 'title', 'sn', 'givenname', 'mail', 'purdueEduCampus'])
				->get();

			if (!empty($results))
			{
				$status = 200;

				foreach ($results as $result)
				{
					/*if ($event->results->getCollection()->count() >= $event->results->total())
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
					$user->email = $result['mail'][0]; //$user->username . '@purdue.edu';

					$event->results->push($user);
				}

				// Update pagination information
				//$items = $event->results->getCollection()->toArray();
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
						'query' => $output/*[
							'page' => $event->results->currentPage()
						]*/
					]
				);

				$event->results = $itemsTransformedAndPaginated;
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'cn=' . $event->search);
	}

	/**
	 * Display user profile info
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleUserBeforeDisplay($event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		try
		{
			$ldap = $this->connect($config);

			$user = $event->getUser();
			$status = 404;

			$results = $ldap->search()
				->orWhere('uid', '=', $user->username)
				->select([
					'title', 'mail', 'roomNumber', 'purdueEduCampus',
					'purdueEduDepartment', 'purdueEduBuilding', 'purdueEduSchool',
					'purdueEduOfficePhone', 'purdueEduOtherPhone'
				])
				->get();

			if (!empty($results))
			{
				$status = 200;

				foreach ($results as $data)
				{
					if (isset($data['title']))
					{
						$user->title = $data['title'][0];
					}

					if (isset($data['mail']))
					{
						$user->mail = $data['mail'][0];
					}

					if (isset($data['roomnumber']))
					{
						$user->roomnumber = $data['roomnumber'][0];
					}

					if (isset($data['purdueeducampus']))
					{
						$user->campus = $data['purdueeducampus'][0];
					}

					if (isset($data['purdueedudepartment']))
					{
						$user->department = $data['purdueedudepartment'][0];
					}

					if (isset($data['purdueedubuilding']))
					{
						$user->building = strtoupper($data['purdueedubuilding'][0]);
					}

					if (isset($data['purdueeduschool']))
					{
						$user->school = $data['purdueeduschool'][0];
					}

					if (isset($data['purdueeduofficephone']))
					{
						$user->phone = $data['purdueeduofficephone'][0];
						$user->phone = preg_replace('/^\+1 /', '', $user->phone);
					}
					elseif (isset($data['purdueeduotherphone']))
					{
						$user->phone = $data['purdueeduotherphone'][0];
						$user->phone = preg_replace('/^\+1 /', '', $user->phone);
					}
				}

				$event->setUser($user);
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('ldap', __METHOD__, 'GET', $status, $results, 'uid=' . $event->getUser()->username);
	}
}
