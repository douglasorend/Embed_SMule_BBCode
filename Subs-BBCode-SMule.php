<?php
/**********************************************************************************
* Subs-BBCode-smule.php
***********************************************************************************
* This mod is licensed under the 2-clause BSD License, which can be found here:
*	http://opensource.org/licenses/BSD-2-Clause
***********************************************************************************
* This program is distributed in the hope that it is and will be useful, but      *
* WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY    *
* or FITNESS FOR A PARTICULAR PURPOSE.                                            *
**********************************************************************************/
if (!defined('SMF')) 
	die('Hacking attempt...');

function BBCode_SMule(&$bbc)
{
	// Format: [smule width=x height=x frameborder=x]{smule ID}[/smule]
	$bbc[] = array(
		'tag' => 'smule',
		'type' => 'unparsed_content',
		'parameters' => array(
			'width' => array('match' => '(\d+)'),
			'frameborder' => array('optional' => true, 'match' => '(\d+)'),
		),
		'validate' => 'BBCode_SMule_Validate',
		'content' => '{width}|{frameborder}',
		'disabled_content' => '$1',
	);

	// Format: [smule width=x height=x frameborder=x]{smule ID}[/smule]
	$bbc[] = array(
		'tag' => 'smule',
		'type' => 'unparsed_content',
		'parameters' => array(
			'frameborder' => array('match' => '(\d+)'),
		),
		'validate' => 'BBCode_SMule_Validate',
		'content' => '0|0|{frameborder}',
		'disabled_content' => '$1',
	);

	// Format: [smule]{smule ID}[/smule]
	$bbc[] = array(
		'tag' => 'smule',
		'type' => 'unparsed_content',
		'validate' => 'BBCode_SMule_Validate',
		'content' => '0|0|0',
		'disabled_content' => '$1',
	);
}

function BBCode_SMule_Button(&$buttons)
{
	$buttons[count($buttons) - 1][] = array(
		'image' => 'smule',
		'code' => 'smule',
		'description' => 'smule',
		'before' => '[smule]',
		'after' => '[/smule]',
	);
}

function BBCode_SMule_Validate(&$tag, &$data, &$disabled)
{
	global $txt, $modSettings;
	
	if (empty($data))
		return ($tag['content'] = $txt['SMule_no_post_id']);
	$data = strtr(trim($data), array('<br />' => ''));
	if (strpos($data, 'http://') !== 0 && strpos($data, 'https://') !== 0)
		$data = 'http://' . $data;
	$pattern = '#(http|https)://(|(.+?).)smule.com/((recording|song)/([\w\d\-\_\%])+/(\d+)_(\d+))#i';
	if (!preg_match($pattern, $data, $parts))
		return ($tag['content'] = $txt['SMule_no_post_id']);
	$data = $parts[4];

	list($width, $frameborder) = explode('|', $tag['content']);
	if (empty($width))
		$width = !empty($modSettings['SMule_default_width']) ? $modSettings['SMule_default_width'] : false;
	$tag['content'] = '<div style="max-width: ' . (empty($width) ? '100%;' : $width . 'px;') . ' height: 125px;"><div class="smule-wrapper">' .
		'<iframe src="https://smule.com/' . $data .'/frame" scrolling="no" frameborder="' . $frameborder . '" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div></div>';
}

function BBCode_SMule_LoadTheme()
{
	global $context, $settings;
	$context['html_headers'] .= '
	<link rel="stylesheet" type="text/css" href="' . $settings['default_theme_url'] . '/css/BBCode-smule.css" />';
	$context['allowed_html_tags'][] = '<iframe>';
}

function BBCode_SMule_Settings(&$config_vars)
{
	$config_vars[] = array('int', 'SMule_default_width');
}

function BBCode_SMule_Embed(&$message, &$smileys, &$cache_id, &$parse_tags)
{
	$replace = (strpos($cache_id, 'sig') !== false ? '[url]$0[/url]' : '[smule]$0[/smule]');
	$pattern = '~(?<=[\s>\.(;\'"]|^)(http|https)://(|(.+?).)smule.com/((recording|song)/([\w\d\-\_\%])+/(\d+)_(\d+))\??[/\w\-_\~%@\?;=#}\\\\]?~';
	$message = preg_replace($pattern, $replace, $message);
	if (strpos($cache_id, 'sig') !== false)
		$message = preg_replace('#\[smule.*\](.*)\[\/smule\]#i', '[url]$1[/url]', $message);
}

?>