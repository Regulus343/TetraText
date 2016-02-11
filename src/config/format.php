<?php

return [

	'encoding' => 'UTF-8',

	'defaults' => [

		'date'     => 'F j, Y',
		'datetime' => 'F j, Y \a\t g:ia',

		'phone' => [
			'digits'              => 10,
			'separator'           => '-',
			'area_code_brackets'  => true,
			'extension_separator' => ' x ',
			'strip_extension'     => false,
		],

		'bool_to_str_options' => 'Yes/No',

		'string_limit' => [
			'words'               => 50,
			'chars'               => 140,
			'trim'                => true,
			'html'                => true,
			'max_words_html'      => 100,
			'max_chars_html'      => 480,
			'exceeded_text'       => '...',
			'exceeded_link_url'   => null,
			'exceeded_link_class' => 'read-more',
		],

		'unique' => [
			'model'       => false,
			'char_limit'  => false,
			'soft_delete' => false,
		],

	],

];
