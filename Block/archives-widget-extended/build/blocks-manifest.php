<?php
// This file is generated. Do not modify it manually.
return array(
	'archives-widget-extended' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aex/archives-widget-extended',
		'version' => '1.0.0',
		'title' => 'Archives Widget Extended',
		'category' => 'widgets',
		'icon' => 'archive',
		'description' => 'An extended date archive list with custom post type and CSS class options.',
		'keywords' => array(
			'archives',
			'posts'
		),
		'textdomain' => 'archives-widget-extended',
		'attributes' => array(
			'displayAsDropdown' => array(
				'type' => 'boolean',
				'default' => false
			),
			'showLabel' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showPostCounts' => array(
				'type' => 'boolean',
				'default' => false
			),
			'type' => array(
				'type' => 'string',
				'default' => 'monthly'
			),
			'postType' => array(
				'type' => 'string',
				'default' => 'post'
			),
			'extraClasses' => array(
				'type' => 'string',
				'default' => ''
			),
			'itemClasses' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'anchor' => true,
			'align' => true,
			'html' => false,
			'spacing' => array(
				'margin' => true,
				'padding' => true
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true
			),
			'color' => array(
				'gradients' => true,
				'link' => true,
				'__experimentalDefaultControls' => array(
					'background' => true,
					'text' => true,
					'link' => true
				)
			),
			'interactivity' => array(
				'clientNavigation' => true
			)
		),
		'example' => array(
			
		),
		'editorScript' => 'file:./index.js',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	)
);
