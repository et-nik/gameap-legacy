<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['post_controller'][] = array(
  'class'    => 'Tpl_replace',
  'function' => 'parse_lang',
  'filename' => 'tpl_replace.php',
  'filepath' => 'hooks'
);

$hook['post_controller'][] = array(
  'class'    => 'Tpl_replace',
  'function' => 'parse_url',
  'filename' => 'tpl_replace.php',
  'filepath' => 'hooks'
);

$hook['post_controller'][] = array(
  'class'    => 'Tpl_replace',
  'function' => 'parse_template',
  'filename' => 'tpl_replace.php',
  'filepath' => 'hooks'
);

$hook['post_controller'][] = array(
  'class'    => 'Tpl_replace',
  'function' => 'parse_notices',
  'filename' => 'tpl_replace.php',
  'filepath' => 'hooks'
);
/* End of file hooks.php */
/* Location: ./application/config/hooks.php */
