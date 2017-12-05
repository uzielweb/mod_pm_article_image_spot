<?php
defined('_JEXEC') or die;
$app = JFactory::getApplication();
$tpath = JURI::base(true) . '/templates/' . $app->getTemplate() . '/';
$widthchoosen = $params->get('widthchoosen');
$heightchoosen = $params->get('heightchoosen');
$cropchoosen = ($params->get('widthchoosen') / $params->get('heightchoosen')) . ':1';
$maxLimit = $params->get('max_limit');
$input = Jfactory::getApplication()->input;
$is_category = 'false';
if ($input->getCmd('option') == 'com_content' && ($input->getCmd('view') == 'categories' or $input->getCmd('view') == 'category' or $input->getCmd('view') == 'featured' or $input->getCmd('view') == 'archive'))
{
		$is_category = 'true';
		$db_cat = JFactory::getDbo();
		$query_cat = $db_cat->getQuery(true);
		$query_cat->select('id, extension, title, params, description');
		$query_cat->from('#__categories');
		$query_cat->where($db_cat->quoteName('extension') . " = " . $db_cat->quote('com_content'));
		$db_cat->setQuery($query_cat);
		$results_cat = $db_cat->loadObjectList();
		foreach ($results_cat as $result_cat)
		{
				$cat_params = json_decode($result_cat->params);
				$choosed_image = $cat_params->image;
				$title = $result_cat->title;
				$text = $result_cat->description;
		}
}
if ($input->getCmd('option') == 'com_content' && $input->getCmd('view') == 'article')
{ //load something from content
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, catid, images, title, introtext');
		$query->from('#__content');
		$query->where($db->quoteName('id') . " = " . $db->quote($input->getInt('id')));
		$db->setQuery($query);
		$results = $db->loadObjectList();
		foreach ($results as $result)
		{
				$article_id = $result->id;
				$catid = $result->catid;
				$images = $result->images;
				$title = $result->title;
				$introtext = $result->introtext;
		}
//load fulltext from content
		$db->setQuery('select c.fulltext, c.created_by, u.name AS author FROM #__content AS c JOIN  #__users AS u ON u.id = c.created_by WHERE c.id=' . $input->getInt('id'));
		$fulltext = $db->loadResult();
		$text = $introtext . $fulltext;
}
$allimages = (json_decode($images));
$custom_image = $params->get('custom_image');
preg_match('/(?<!_)src=([\'"])?(.*?)\\1/', $text, $matches);
$article_image = $matches[2];
$thumbsnippet = 'modules/mod_' . $module->name . '/assets/smart/image.php?width=' . $widthchoosen . '&height=' . $heightchoosen . '&cropratio=' . $cropchoosen . '&image=' . JURI::root();
if (($params->get('image_in_the_spot') == 'intro_image') and (!empty($allimages->image_intro)))
{
		$choosed_image = $allimages->image_intro;
}
if (($params->get('image_in_the_spot') == 'full_image') and (!empty($allimages->image_fulltext)))
{
		$choosed_image = $allimages->image_fulltext;
}
if (($params->get('image_in_the_spot') == 'custom_image') and (!empty($custom_image)))
{
		$choosed_image = $params->get('custom_image');
}
if (($params->get('image_in_the_spot') == 'article_image') and (!empty($article_image)))
{
		$choosed_image = $article_image;
}
JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php'); //load fields helper
$customFieldnames = FieldsHelper::getFields('com_content.article', $article_id, true); // get custom field names by article id
$customFieldIds = array_map(create_function('$o', 'return $o->id;'), $customFieldnames); //get custom field Ids by custom field names
$model = JModelLegacy::getInstance('Field', 'FieldsModel', array('ignore_request' => true)); //load fields model
$customFieldValues = $model->getFieldValues($customFieldIds, $article_id); //Fetch values for custom field Ids
$custom_field_image = $params->get('custom_field_image');
$recount = '0';
foreach ($customFieldnames as $count_field => $field)
{
		$recount++;
		foreach ($customFieldValues as $count_value => $field_value)
		{
				if ($count_value == $recount)
				{
						if ($field->title == $params->get('custom_field_image'))
						{
								$choosed_image = $field_value;
						}
						if ($field->title == $params->get('custom_field_title'))
						{
								$title = $field_value;
						}
						if ($field->title == $params->get('custom_field_text'))
						{
								$text = $field_value;
						}
				}
		}
}
$text = preg_replace("/\r\n|\r|\n/", " ", $text);
// Next, replace <br /> tags with \n
$text = preg_replace("/<BR[^>]*>/i", " ", $text);
// Replace <p> tags with \n\n
$text = preg_replace("/<P[^>]*>/i", " ", $text);
// Strip all tags
if ($params->get('strip_tags') == '1')
{
		$text = strip_tags($text);
}
// Truncate
if ($maxLimit > 0)
{
		$text = substr($text, 0, $maxLimit);
//$text = String::truncate($text, $maxLimit, '...', true);
// Pop off the last word in case it got cut in the middle
		$text = preg_replace("/[.,!?:;]? [ ^ ]*$/", " ", $text);
// Add ... to the end of the article.
		$text = trim($text) . '...';
}
$background_spot = '';
if ($params->get('background_or_src') == 'background')
{
		$background_spot = 'background: url(' . $thumbsnippet . $choosed_image . ') no-repeat top center / cover;';
}
?>
<?php
$disable_spot = 'false';
if ($choosed_image = '')
{
		$disable_spot = 'true';
}
if ($params->get('show_in_articles') == '0' and ($is_category == 'false'))
{
		$disable_spot = 'true';
}
if ($params->get('show_in_categories') == '0' and ($is_category == 'true'))
{
		$disable_spot = 'true';
}
?>
<?php if ($disable_spot == 'false') : ?>
<?php if ($input->getCmd('option') == 'com_content' && ($input->getCmd('view') == 'article') or ($is_category == 'true')) : ?>

<div id="spot_<?php echo $module->id; ?>" class="image_spot" style="height: <?php echo $params->get('height'); ?>;<?php echo $background_spot; ?>">
     <?php if (($params->get('show_title') != 'none') or ($params->get('show_text') != 'none') or ($params->get('show_cat_title') == '1') or ($params->get('show_cat_title') == '1')) : ?>
     <div class="info_spot">
    <?php if (($params->get('show_title') != 'none') and ($is_category == 'false')) : ?>
     <div class="title_spot article_spot_title">
       <h4><?php echo $title; ?></h4>
     </div>
     <?php endif; ?>

      <?php if (($params->get('show_text') != 'none') and ($is_category == 'false')) : ?>
     <div class="text_spot article_spot_text">
       <?php echo $text; ?>
     </div>
     <?php endif; ?>

       <?php if (($params->get('show_cat_title') == '1') and ($is_category == 'true')) : ?>
     <div class="title_spot cat_spot_title">
       <h4><?php echo $title; ?></h4>
     </div>
     <?php endif; ?>

      <?php if (($params->get('show_cat_text') == '1') and ($is_category == 'true')) : ?>
     <div class="text_spot cat_spot_text">
       <?php echo $text; ?>
     </div>
     <?php endif; ?>
     <?php if ($params->get('background_or_src') == 'src') : ?>
     <div class="src_image_spot">
         <img src="<?php echo $thumbsnippet . $choosed_image; ?>" alt="<?php echo $title; ?>" />
     </div>
    <?php endif; ?>
    </div>
      <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>
