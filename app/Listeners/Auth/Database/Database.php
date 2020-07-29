<?php
namespace App\Listeners\Auth\Database;

/**
 * Batabase-based authentication plugin
 */
class Database
{
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array    $credentials  Array holding the user credentials
	 * @param   array    $options      Array of extra options
	 * @param   object   $response     Authentication response object
	 * @return  boolean
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		// For Log
		$response->type = 'database';

		// HUBzero does not like blank passwords
		if (empty($credentials['password']))
		{
			$response->status = \Halcyon\Auth\Status::FAILURE;
			$response->error_message = trans('listener.auth.database::database.no password');
			return false;
		}

		// Initialize variables
		$conditions = '';

		// Determine if attempting to log in via username or email address
		if (strpos($credentials['username'], '@'))
		{
			$conditions = ' WHERE email=' . $db->Quote($credentials['username']);
		}
		else
		{
			$conditions = ' WHERE username=' . $db->Quote($credentials['username']);
		}

		$query = 'SELECT `id`, `username`, `password`'
				. ' FROM `#__users`'
				. $conditions
				. ' AND `block` != 1';

		$db->setQuery($query);

		$result = $db->loadObjectList();

		if (is_array($result) && count($result) > 1)
		{
			$response->status = \Halcyon\Auth\Status::FAILURE;
			$response->error_message = trans('PLG_AUTHENTICATION_HUBZERO_UNKNOWN_USER');
			return false;
		}
		elseif (is_array($result) && isset($result[0]))
		{
			$result = $result[0];
		}
		else
		{
			$response->status = \Halcyon\Auth\Status::FAILURE;
			$response->error_message = trans('PLG_AUTHENTICATION_HUBZERO_AUTHENTICATION_FAILED');
			return false;
		}

		// Remove old records
		if ($duration = \Component::params('com_members')->get('login_log_timeframe'))
		{
			$authlog = \Halcyon\User\Log\Auth::blank();
			$authlog->delete($authlog->getTableName())
				->where('logged', '<', Date::of('now')->modify('-' . $duration)->toSql())
				->execute();
		}

		// Check to see if there are many blocked accounts
		if ($this->hasExceededBlockLimit($result))
		{
			// Might be a moot point if Fail2Ban is triggered
			$response->status = \Halcyon\Auth\Status::FAILURE;
			$response->error_message = trans('PLG_AUTHENTICATION_HUBZERO_TOO_MANY_ATTEMPTS');
			return false;
		}

		// Now make sure they haven't made too many failed login attempts
		if ($this->hasExceededLoginLimit(\Halcyon\User\User::oneOrFail($result->id)))
		{
			$response->status = \Halcyon\Auth\Status::FAILURE;
			$response->error_message = trans('PLG_AUTHENTICATION_HUBZERO_TOO_MANY_ATTEMPTS');
			return false;
		}

		if ($result)
		{
			if (\Halcyon\User\Password::passwordMatches($result->username, $credentials['password'], true))
			{
				$user = User::getInstance($result->id);

				$response->username      = $user->get('username');
				$response->email         = $user->get('email');
				$response->fullname      = $user->get('name');
				$response->status        = \Halcyon\Auth\Status::SUCCESS;
				$response->error_message = '';

				// Check validity and age of password
				$password_rules = \Halcyon\Password\Rule::all()
					->whereEquals('enabled', 1)
					->rows();
				$msg = \Halcyon\Password\Rule::verify($credentials['password'], $password_rules, $result->username, null, false);
				if (is_array($msg) && !empty($msg[0]))
				{
					App::get('session')->set('badpassword', '1');
				}
				if (\Halcyon\User\Password::isPasswordExpired($result->username))
				{
					App::get('session')->set('expiredpassword', '1');
				}

				// Set cookie with login preference info
				$prefs = array(
					'user_id'       => $user->get('id'),
					'user_img'      => $user->picture(0, false),
					'authenticator' => 'hubzero'
				);

				$namespace = 'authenticator';
				$lifetime  = time() + 365*24*60*60;

				\Halcyon\Utility\Cookie::bake($namespace, $lifetime, $prefs);
			}
			else
			{
				$response->status = \Halcyon\Auth\Status::FAILURE;
				$response->error_message = trans('PLG_AUTHENTICATION_HUBZERO_AUTHENTICATION_FAILED');
			}
		}
		else
		{
			$response->status = \Halcyon\Auth\Status::FAILURE;
			$response->error_message = trans('PLG_AUTHENTICATION_HUBZERO_AUTHENTICATION_FAILED');
		}
	}
}
