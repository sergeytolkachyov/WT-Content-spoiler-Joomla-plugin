<?php
/**
 * @package     WT Content spoiler
 * @version     1.0.0
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2022 Sergey Tolkachyov
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
 * @since       1.0.0
 */

// No direct access
namespace Joomla\Plugin\Content\Wt_content_spoiler\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;

defined('_JEXEC') or die;


class Wt_content_spoiler extends CMSPlugin
{
	protected $autoloadLanguage = true;

	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{

		if ($context == 'com_finder.indexer')
		{
			return false;
		}

		// Don't run if there is no text property (in case of bad calls) or it is empty
		if (empty($article->text))
		{
			return;
		}
		$regex = "~{spoiler}.*?{/spoiler}~is";

		// replacements for content to avoid issues with RegEx
		$article->text = str_replace('~', '&#126;', $article->text);
		Factory::getApplication()->getLanguage()->load('plg_content_wt_content_spoiler', JPATH_PLUGINS . '/content/wt_content_spoiler');
		// process tags
		if (preg_match_all($regex, $article->text, $matches, PREG_PATTERN_ORDER))
		{
			$iterator = 1;
			// start the replace loop
			foreach ($matches[0] as $key => $match)
			{

				$tagcontent = preg_replace("/{.+?}/", "", $match);
				$tagcontent = str_replace(array('"', '\'', '`'), array('&quot;', '&apos;', '&#x60;'), $tagcontent); // Address potential XSS attacks

				$path = PluginHelper::getLayoutPath('content', 'wt_content_spoiler');
				ob_start();
				include $path;
				ob_get_contents();
				$html          = ob_get_clean();
				$article->text = str_replace($match, $html, $article->text);
				$iterator++;
			}

			// Добавляем js
			$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
			$js = <<<JS
					document.addEventListener('DOMContentLoaded', function (){
						let wt_content_spoiler_options = Joomla.getOptions('wt_content_spoiler_options');
						let all_spoilers = document.querySelectorAll('[data-wt-content-spoiler-toggler]');
						all_spoilers.forEach(function (element,i,array){
							element.addEventListener('click',function (event){
								let spoiler_id = element.getAttribute('data-wt-content-spoiler-toggler');
								let spoiler_container = document.querySelector('#wt-content-spoiler-'+ spoiler_id + ' .wt-content-spoiler-inner');
								console.log(spoiler_id);
								console.log(spoiler_container);
								if(spoiler_container.classList.contains('open')){
									spoiler_container.classList.remove('open');
									element.innerHTML = wt_content_spoiler_options.show;
								} else {
									spoiler_container.classList.add('open');
									element.innerHTML = wt_content_spoiler_options.hide;
								}
							});
						});
						
					});
					JS;
			$wa->addInlineScript($js);
			if (!empty($this->params->get('spoiler_css')))
			{
				$css = $this->params->get('spoiler_css');
			}
			else
			{
				$css = '.wt-content-spoiler-inner {
					position: relative;
					overflow: hidden;
					height: 70px;
				}
				.wt-content-spoiler-inner.open {
					transition: .3s all easy-in-out;
					height: 100%;
				}
			';
			}

			$wa->addInlineStyle($css);
			$btn_show_hide_text = [
				'show' => (empty($this->params->get('btn_text_show', 'PLG_WT_CONTENT_SPOILER_BTN_READMORE_SHOW'))) ? Text::_($this->params->get('btn_text_show', 'PLG_WT_CONTENT_SPOILER_BTN_READMORE_SHOW')) : Text::_('PLG_WT_CONTENT_SPOILER_BTN_READMORE_SHOW'),
				'hide' => (empty($this->params->get('btn_text_hide', 'PLG_WT_CONTENT_SPOILER_BTN_READMORE_HIDE'))) ? Text::_($this->params->get('btn_text_hide', 'PLG_WT_CONTENT_SPOILER_BTN_READMORE_HIDE')) : Text::_('PLG_WT_CONTENT_SPOILER_BTN_READMORE_HIDE'),
			];
			Factory::getApplication()->getDocument()->addScriptOptions('wt_content_spoiler_options', $btn_show_hide_text);

		}

	}
}
