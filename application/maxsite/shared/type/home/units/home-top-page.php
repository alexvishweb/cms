<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * MaxSite CMS
 * (c) https://max-3000.com/
 */

$par = [
	'page_id' => mso_get_option('home_page_id_top', 'templates', '0'),
	'cut' => mso_get_option('more', 'templates', 'Читать полностью »'),
	'cat_order' => 'category_name',
	'cat_order_asc' => 'asc',
	'pagination' => false,
	'exclude_page_id' => mso_get_val('exclude_page_id', []),
];

// подключаем кастомный вывод, где можно изменить массив параметров $par для своих задач
if ($fn = mso_page_foreach('home-top-mso-get-pages')) require $fn;

$pages = mso_get_pages($par, $temp);

mso_set_val('container_class', 'mso-home-top-page');
mso_set_val('full_format_title_start', '<h1>');
mso_set_val('full_format_title_end', '</h1>');

if ($fn = mso_find_ts_file('type/_def_out/full/full.php')) require $fn;

# end of file
