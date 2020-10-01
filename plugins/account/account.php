<?php

gonzo::import('table');
gonzo::import('link');
gonzo::import('session-native');
gonzo::import('form');

gonzo::var('account.activation', 'off');
gonzo::var('account.invitation', 'off');

gonzo::var('account.url', '/~:name');
gonzo::var('account.landing', '/');
gonzo::var('account.admin_url', '/admin');
gonzo::var('account.login_url', '/login');
gonzo::var('account.register_url', '/register');
gonzo::var('account.logout_url', '/logout');
gonzo::var('account.session_time', 3600 * 24);

global $account;

$account = table_register(array
(
	'name' => 'account',
	'table_prefix' => 'gonzo_'
), array
(
	'fields' => array
	(
		'name' => '',
		'password' => '',
		'email' => '',
		'date' => '',
		'modified' => '',
		'status' => 'active',
		'permissions' => '',
		'avatar' => '',
		'realname' => '',
		'description' => '',
		'website' => ''
	),
	'unique' => array('name', 'email'),
	'search' => array('name' => 2, 'email' => 1),
	'hash' => array('password'),
	'rules' => array
	(
		'*' => array('filter' => 'santize'),
		'name' => array('required' => true, 'filter' => 'slug', 'min_length' => 3, 'alpha_numeric' => true),
		'password' => array('required' => true, 'min_length' => 6),
		'confirm_password' => array('match' => 'password'),
		'email' => array('required' => true, 'email' => true)
	),
	'types' => array
	(
		'name' => 'varchar(64)',
		'password' => 'varchar(128)',
		'email' => 'varchar(128)',
		'date' => 'varchar(64)',
		'modified' => 'varchar(64)',
		'status' => 'varchar(16)',
		'permissions' => 'text',
		'avatar' => 'varchar(128)',
		'realname' => 'varchar(128)',
		'description' => 'varchar(255)',
		'website' => 'varchar(128)'
	),
	'feature_tables' => array()
));

register_form('account.login', 'account.login', __('Login'), 0);
register_form_field('account.login', 'account.login', 'name', array('label' => __('Name'), 'type' => 'text', 'name' => 'name', 'autocomplete' => 'off'), 5);
register_form_field('account.login', 'account.login', 'password', array('label' => __('Password'), 'type' => 'password', 'name' => 'password', 'autocomplete' => 'off'), 10);
register_form_field('account.login', 'account.login', 'submit', array('label' => __('Login'), 'type' => 'submit', 'name' => 'login', 'value' => 'login', 'class' => 'r'), 20);

register_form('account.register', 'account.register', __('Register'), 0);
register_form_field('account.register', 'account.register', 'name', array('label' => __('Name'), 'type' => 'text', 'name' => 'name', 'autocomplete' => 'off'), 5);
register_form_field('account.register', 'account.register', 'email', array('label' => __('Email'), 'type' => 'text', 'name' => 'email', 'autocomplete' => 'off'), 10);
register_form_field('account.register', 'account.register', 'password', array('label' => __('Password'), 'type' => 'password', 'name' => 'password', 'autocomplete' => 'off'), 15);
register_form_field('account.register', 'account.register', 'confirm_password', array('label' => __('Confirm password'), 'type' => 'password', 'name' => 'confirm_password', 'autocomplete' => 'off'), 20);
register_form_field('account.register', 'account.register', 'submit', array('label' => __('Register'), 'type' => 'submit', 'name' => 'register', 'value' => 'register', 'class' => 'r'), 30);

gonzo::on('gonzo.dispatch', function()
{
	$session = gonzo::instance('session');

	$session->create(gonzo::var('account.session_time'));
});

gonzo::on(gonzo::var('account.login_url'), function($data)
{
	global $account;

	$session = gonzo::instance('session');

	if ($session->get('account_name') !== false)
	{
		gonzo::http_rdir(gonzo::var('account.landing'));

		return '';
	}

	if (gonzo::input('post', 'login') == 'login' && gonzo::input('post', 'name'))
	{
		$accounts = table_row_get($account, array('name' => gonzo::input('post', 'name'), 'status' => 'active', 'per_page' => 1));

		if (is_array($accounts) && count($accounts) == 1 && filter_hash(gonzo::input('post', 'password')) == $accounts[0]['password'])
		{
			$session->set('account_id', $accounts[0]['id']);
			$session->set('account_name', $accounts[0]['name']);
			$session->set('account_email', $accounts[0]['email']);
			$session->set('account_permissions', explode(' ', $accounts[0]['permissions']));

			gonzo::http_rdir(gonzo::var('account.landing'));
		} else
		{
			$session->destroy();

			valid_error('name', 'validator.wrong_name_or_password', 'table.account');
		}
	}

	return gonzo::view('login');
});

gonzo::on(gonzo::var('account.register_url'), function($data)
{
	global $account;

	$session = gonzo::instance('session');

	if ($session->get('account_name') !== false)
	{
		gonzo::http_rdir(gonzo::var('account.landing'));

		return '';
	}

	if (gonzo::input('post', 'register') == 'register')
	{
		$add = table_row_add($account, gonzo::input('post'));

		if (is_numeric($add) && $add > 0)
		{
			return gonzo::view('registered');
		}
	}

	return gonzo::view('register');
});

gonzo::on(gonzo::var('account.logout_url'), function($data)
{
	global $account;

	$session = gonzo::instance('session');

	$session->destroy();

	gonzo::http_rdir('/');

	return '';
});
