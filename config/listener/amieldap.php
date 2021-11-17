<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Amie LDAP credentials
    |--------------------------------------------------------------------------
    */
    'ldap' => [
        'hosts'    => ['amie.anvil.rcac.purdue.edu'],
        'use_ssl'  => false,
        'base_dn'  => env('LDAP_AMIE_BASEDN', conf('ldap_amie', 'basedn', 'dc=anvil,dc=rcac,dc=purdue,dc=edu')),
        'username' => env('LDAP_AMIE_USERNAME', conf('ldap_amie', 'rdn', 'cn=halcyon,dc=rcac,dc=purdue,dc=edu')),
        'password' => env('LDAP_AMIE_PASSWORD', conf('ldap_amie', 'pass', '')),
    ],

    /*
	|--------------------------------------------------------------------------
	| New User role
	|--------------------------------------------------------------------------
	|
	| The user role to apply to newly created accounts.
	|
	*/
	'new_usertype' => 15,
];
