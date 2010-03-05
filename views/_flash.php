<?php
/*
 * CSS suggestion:
 * 
 *   #notices { background-color: #fffadd; border: 2px solid #fae28e; margin: 0 0 24px; padding: 3px 12px; }
 *   #notices h3 { background-position: left center; background-repeat: no-repeat; border-bottom: none; font-weight: bold; margin-bottom: 0; }
 *   #notices.error h3 { background-image: url('/images/icon_error.gif'); padding: 12px 0 12px 44px; }
 *   #notices.confirmation h3 { background-image: url('/image/icon_confirm.gif'); line-height: 1.5; padding: 8px 0 8px 44px; }
 *   #notices.confirmation { background-color: #eefce7; border: 2px solid #caedba; }
 *   #notices  li { line-height: 2em; margin-left: 12px; }
*/

if ( ! empty($error))
{
	$heading = $error;
	$class = 'error';
}

if ( ! empty($errors) AND is_array($errors))
{
	$list = $errors;
	$class = 'error';
}

if ( ! empty($confirm))
{
	$heading = $confirm;
	$class = 'confirmation';
}

$id = empty($id) ? 'notices' : $id;

$class = empty($class) ? '' : $class;

echo '<div id="', $id, '" class="', $class, '">', "\n";

	if (isset($heading)) echo '<h3>', $heading, "</h3>\n";
	
	// if $text doesn't start with a tag we wrap it in a <p>
	if (isset($text)) echo ($text[0] == '<') ? $text : "<p>$text</p>", "\n";
	
	if (isset($list) AND count($list))
	{
		echo "<ol>\n";
	
		foreach($list as $item)
		{
			echo "<li>$item</li>\n";
		}
	
		echo "</ol>\n";
	}
	
echo '</div>';


