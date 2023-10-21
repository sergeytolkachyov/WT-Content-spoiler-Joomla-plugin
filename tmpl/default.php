<?php

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

extract($displayData);

?>

<div class="wt-content-spoiler" id="wt-content-spoiler-<?php echo $iterator;?>">
	<div class="wt-content-spoiler-inner">
		<?php echo $tagcontent;?>
	</div>
	<button type="button" class="<?php echo $params->get('btn_css_class','btn btn-link');?>" data-wt-content-spoiler-toggler="<?php echo $iterator;?>"><?php echo Text::_('PLG_WT_CONTENT_SPOILER_BTN_READMORE_SHOW');?></button>
</div>
