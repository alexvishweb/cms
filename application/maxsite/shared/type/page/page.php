<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * MaxSite CMS
 * (c) http://max-3000.com/
 */

mso_page_view_count_first(); // для подсчета количества прочтений страницы

$par = array( 
	'cut' => false, 
	'cat_order' => 'category_id_parent', 
	'cat_order_asc' => 'asc', 
	'type' => false); 

if ($f = mso_page_foreach('page-mso-get-pages')) require($f); 

$pages = mso_get_pages($par, $pagination); // получим все

if ($f = mso_page_foreach('page-head-meta')) 
{
	require($f);
}
else
{ 
	mso_head_meta('title', $pages, '%page_title%'); // meta title страницы
	mso_head_meta('description', $pages); // meta description страницы
	mso_head_meta('keywords', $pages); // meta keywords страницы
}
	
	
if (!$pages and mso_get_option('page_404_http_not_found', 'templates', 1) ) 
	header('HTTP/1.0 404 Not Found'); 

if ($f = mso_page_foreach('page-main-start')) 
{
	require($f);
	return;
}
else
{
	if ($fn = mso_find_ts_file('main/main-start.php')) require($fn);
}

if (!mso_get_val('page_content_only', false)) echo NR . '<div class="mso-type-page">' . NR;

if ($f = mso_page_foreach('page-do')) require($f);

if ($pages)
{ 	
	// только контент в чистом виде
	if (mso_get_val('page_content_only', false))
	{
		foreach ($pages as $page)
		{
			if ($f = mso_page_foreach('page-content-only')) 
			{
				require($f);
				continue;
			}
			
			echo $page['page_content'];
		}
	}
	else
	{
		// полноценный вывод
		
		$p = new Page_out();

		$p->format('title', '<h1>', '</h1>', false);
		$p->format('date', 'D, j F Y г.', '<span><time datetime="[page_date_publish_iso]">', '</time></span>');
		$p->format('cat', ' -&gt; ', '<br><span>' . tf('Рубрика') . ': ', '</span>');
		$p->format('tag', ' | ', '<br><span>' . tf('Метки') . ': ', '</span>');
		//$p->format('feed', tf('Комментарии по RSS'), ' | <span>', '</span>');
		$p->format('edit', 'Edit', ' | <span>', '</span>');
		$p->format('view_count', '<br><span>' . tf('Просмотров') . ': ', '</span>');

		if ($f = mso_page_foreach('format-page')) require($f);
		
		foreach ($pages as $page)
		{
			if ($f = mso_page_foreach('page')) 
			{
				require($f);
				
				// здесь комментарии
				if ($fn = mso_find_ts_file('type/page/units/page-comments.php')) require($fn);

				continue;
			}
			
			$p->load($page);
			
			if ($f = mso_page_foreach('page-do-page-only')) require($f);
				
			$p->div_start('mso-page-only', '<article>');
			
				// у page в записи может быть метаполе info-top-custom
				// где указываетеся свой файл вывода
				// файл указывается в type_foreach/info-top/файл.php
				$info_top_custom = $p->meta_val('info-top-custom');

				if ($info_top_custom and $f = mso_fe('type_foreach/info-top/' . $info_top_custom) )
				{
					require($f);
				}
				else // нет метаполя - типовой вывод
				{
					// для разных page может быть свой info-top
					if ($f = mso_page_foreach('info-top-page-status-' . $p->val('page_status'))) 
					{
						require($f);
					}
					elseif ($f = mso_page_foreach('info-top-page-type-' . $p->val('page_type_name'))) 
					{
						require($f);
					}
					elseif ($info = mso_get_option('info-top_page', getinfo('template'), '') and $f = mso_fe('type_foreach/info-top/' . $info))
					{
						require($f);
					}
					elseif ($f = mso_page_foreach('info-top-page')) 
					{
						require($f);
					}
					elseif ($f = mso_page_foreach('info-top'))
					{
						require($f);
					}
					else
					{
						$p->html(NR . '<header>');
							$p->line('[title]');
							
							$p->div_start('mso-info mso-info-top');
								$p->line('[date][edit][cat][tag][view_count]');
							$p->div_end('mso-info mso-info-top');
						$p->html('</header>');
					}
					
				}
				
				if ($f = mso_page_foreach('page-content-' . getinfo('type'))) 
				{
					require($f);
				}
				else
				{
					if ($f = mso_page_foreach('page-content')) 
					{
						require($f);
					}
					else
					{
						$add_class_page = '';
						
						if ($add1 = $p->meta_val('mso-page-content-add-class', '', '')) 
							$add_class_page .= ' ' . $add1;
						
						if ($add2 = mso_get_val('mso-page-content-add-class', ''))
							$add_class_page .= ' ' . $add2;
						
						if ($add_class_page) $add_class_page = ' ' . trim($add_class_page);
							
						$p->div_start('mso-page-content mso-type-' . getinfo('type') . '-content' . $add_class_page);
							
							if ($f = mso_page_foreach('content')) require($f);
							else
							{
								// есть отметка выводить миниатюру
								if ($p->meta_val('image_for_page_out') === '' or $p->meta_val('image_for_page_out') === 'page')
								{
									// если show_thumb_type_ТИП вернул false, то картинку не ставим
									// show_thumb - если нужно отключить для всех типов
									if ( mso_get_val('show_thumb', true)
										and mso_get_val('show_thumb_type_' . getinfo('type'), true) )
									{

										// вывод миниатюры перед записью
										if ($image_for_page = thumb_generate(
												$p->meta_val('image_for_page'), 
												mso_get_option('image_for_page_width', getinfo('template'), 280),
												mso_get_option('image_for_page_height', getinfo('template'), 210),
												false, 
												'resize_full_crop_center', 
												false, 
												'mini', 
												true, 
												mso_get_option('upload_resize_images_quality', 'general', 90)
											))
										{
											echo $p->img($image_for_page, mso_get_option('image_for_page_css_class', getinfo('template'), 'image_for_page'), '', $p->val('page_title'));
										}
									}
								}
								
								$p->content('', '');
								$p->clearfix();
							}
							
							// для page возможен свой info-bottom
							if ($f = mso_page_foreach('mso-info-bottom-page')) 
							{
								require($f);
							}
							elseif ($f = mso_page_foreach('info-bottom')) require($f);
							
							
							$p->html('<aside>');
								
								mso_page_content_end();
								
								$p->clearfix();
								
								if ($f = mso_page_foreach('page-content-end')) require($f);
								
								// связанные страницы по родителям
								if ($page_nav = mso_page_nav($p->val('page_id'), $p->val('page_id_parent')))
									$p->div($page_nav, 'mso-page-parents');
								
								// блок "Еще записи по теме"
								if ($p->meta_val('show_page_other_pages', 1))
								{
									if ($f = mso_page_foreach('page-other-pages')) 
										require($f);
									else 
										mso_page_other_pages($p->val('page_id'), $p->val('page_categories'));
								}
									
							$p->html('</aside>');
							
						$p->div_end('mso-page-content mso-type-' . getinfo('type') . '-content');
					}
				}
				
			$p->div_end('mso-page-only', '</article>');
			
			if ($f = mso_page_foreach('page-only-end')) require($f);
			
			// здесь комментарии
			
			if ($f = mso_page_foreach('page-comments-start')) require($f);
			
			
			if (mso_get_option('comment_other_system', 'general', false)) 
			{
				// внешнее комментирование 
				if ($fn = mso_find_ts_file('type/page/units/page-comments-other-system.php')) require($fn);
				
				// + стандартное комментирование
				if (mso_get_option('comment_other_system_standart', 'general', false))
				{
					if ($fn = mso_find_ts_file('type/page/units/page-comments.php')) require($fn);
				}
			}
			elseif (mso_hook_present('page-comment-unit-file') and $fn = mso_fe(mso_hook('page-comment-unit-file'), '')) require($fn);
			elseif ($fn = mso_find_ts_file('type/page/units/page-comments.php')) require($fn);
			
			if ($f = mso_page_foreach('page-comments-end')) require($f);
			
				
		} // end foreach
	} // else page_content_only
}
else 
{
	if ($f = mso_page_foreach('pages-not-found')) 
	{
		require($f); // подключаем кастомный вывод
	}
	else // стандартный вывод
	{
		echo '<div class="mso-page-only"><div class="mso-page-content mso-type-page_404">';
		echo '<h1>' . tf('404. Ничего не найдено...') . '</h1>';
		echo '<p>' . tf('Извините, ничего не найдено') . '</p>';
		echo mso_hook('page_404');
		echo '</div></div>';
	}
} // endif $pages

if ($f = mso_page_foreach('page-posle')) require($f);

if (!mso_get_val('page_content_only', false)) echo NR . '</div><!-- /div.mso-type-page -->' . NR;

if ($fn = mso_find_ts_file('main/main-end.php')) require($fn);
	
# end file