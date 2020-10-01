<?php

echo
form
(
	div
	(
		div
		(
			fieldset(legend(__('Register')).p('Thanks for registering.').button(__('Login'), array(/*'name' => 'name', 'value' => 'username', */'class' => 'r')), 'vr b1 ds'),
			'w4 c'
		),
		'g gg g8 svv2'
	),
	array('method' => 'get', 'action' => gonzo::url('/login')),
	't1'
);
