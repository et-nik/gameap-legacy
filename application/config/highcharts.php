<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Template Example
$config['chart_template'] = array(
	'chart' => array(
		'renderTo' => 'graph',
		'defaultSeriesType' => 'column',
     ),
     'credits' => array(
     	'enabled'=> true,
     	'text'	=> 'highcharts library on GitHub',
		'href' => 'https://github.com/ronan-gloo/codeigniter-highcharts-library'
     ),
     'title' => array(
		'text' => 'Template from config file'
     ),
     'legend' => array(
     	'enabled' => false
     ),
    'yAxis' => array(
		'title' => array(
			'text' => '%'
		)
	),
	'xAxis' => array(
		'title' => array(
			'text' => ''
		)
	),
	'tooltip' => array(
		'shared' => true
	)
);
