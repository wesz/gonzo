<?php

include('gonzo.php');

gonzo::var('gonzo.debug', 'off');
gonzo::var('db.debug', 'off');
gonzo::var('gonzo.cache', 'on');

gonzo::run(array
(
	'core',
	'account'
));

