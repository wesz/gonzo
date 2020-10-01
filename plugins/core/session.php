<?php

abstract class Session
{
	public function __construct() {}
	public function __destruct() {}
	public function create() {}
	public function destroy() {}
	public function get($key) {}
	public function set($key, $value = NULL) {}
	public function clear($key) {}
	public function exists($key) {}
}
