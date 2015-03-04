<?php

return [

	'encoding' => 'UTF-8',

	'defaults' => [

		'date'     => 'F j, Y',
		'datetime' => 'F j, Y \a\t g:ia',

		'phone' => [
			'digits'             => 10,
			'separator'          => '-',
			'area_code_brackets' => true,
		],

		'bool_to_str_options' => 'Yes/No',

	],

];
