<?php

gonzo::import('account');

function account_login($name, $password)
{

}

function account_register($name, $email, $password, $confirm_password)
{

}

function account_logged_in($name = '')
{
	$session = gonzo::instance('session');

	return ($session->get('account_name') !== false && (empty($name) || $session->get('account_name') == $name));
}

function account_get($key)
{
	$session = gonzo::instance('session');

	if (account_logged_in() && $session->exists($key))
	{
		return $session->get($key);
	}

	return '';
}

function account_id()
{
	return account_get('account_id');
}

function account_name()
{
	return account_get('account_name');
}

function account_email()
{
	return account_get('account_email');
}

function account_url()
{
	$session = gonzo::instance('session');

	if (account_logged_in())
	{
		return gonzo::url(str_replace(':name', $session->get('account_name'), gonzo::var('account.url')));
	}

	return '';
}

function account_can($permission)
{
	if (account_logged_in())
	{
		if (is_array($permission))
		{
			foreach ($permission as $perm)
			{
				if (in(account_get('account_permissions'), $perm))
				{
					return true;
				}
			}
		} else
		{
			return in(account_get('account_permissions'), $permission);
		}
	}

	return false;
}

function account_avatar()
{

}

function account_realname()
{

}

function account_description()
{

}

function account_website()
{

}

function account_required()
{
	if ( ! account_logged_in())
	{
		gonzo::http_rdir(gonzo::var('account.login_url'));

		return false;
	}

	return true;
}

function account_in_admin()
{
	$request_url = gonzo::url(gonzo::request_uri());
	$admin_url = gonzo::url(gonzo::var('account.admin_url'));

	if (substr($request_url, 0, strlen($admin_url)) == $admin_url)
	{
		return true;
	}

	return false;
}
