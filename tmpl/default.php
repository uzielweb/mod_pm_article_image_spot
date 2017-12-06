<?php
defined('_JEXEC') or die;
$app = JFactory::getApplication();

$input = JFactory::getApplication()->input;

// should be article, categories, featured, blog...
$view = $input->get('view');

// if it's a category view, this will be the category id, else the article id
$id = $input->getInt('id');

// in an article view, this will be the cat id.
$categoryid = $input->getInt('catid');

$article = JControllerLegacy::getInstance('Content')->getModel('Article')->getItem();


$tpath = JURI::base(true) . '/templates/' . $app->getTemplate() . '/';

$widthchoosen = $params->get('widthchoosen');
$heightchoosen = $params->get('heightchoosen');
$cropchoosen = ($params->get('widthchoosen') / $params->get('heightchoosen')) . ':1';
$thumbsnippet = 'modules/mod_' . $module->name . '/assets/smart/image.php?width=' . $widthchoosen . '&height=' . $heightchoosen . '&cropratio=' . $cropchoosen . '&image=' . JURI::root();
 $height = $params->get('height');
$maxLimit = $params->get('max_limit');

//HEX to RGBA
function hex2rgba($color, $opacity = false) {

    $default = 'rgb(0,0,0)';

    //Return default if no color provided
    if(empty($color))
                    return $default;

    //Sanitize $color if "#" is provided
                if ($color[0] == '#' ) {
                	$color = substr( $color, 1 );
                }

                //Check if color has 6 or 3 characters and get values
                if (strlen($color) == 6) {
                                $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
                } elseif ( strlen( $color ) == 3 ) {
                                $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
                } else {
                                return $default;
                }

                //Convert hexadec to rgb
                $rgb =  array_map('hexdec', $hex);

                //Check if opacity is set(rgba or rgb)
                if($opacity){
                	if(abs($opacity) > 1)
                		$opacity = 1.0;
                	$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
                } else {
                	$output = 'rgb('.implode(",",$rgb).')';
                }

                //Return rgb(a) color string
                return $output;
}
$color = $params->get('text_background');
$rgb = hex2rgba($color);
$rgba = hex2rgba($color, $params->get('background_opacity'));
$text_color = $params->get('text_color');
$rgb_text = hex2rgba($text_color);
$rgba_text = hex2rgba($text_color, $params->get('text_opacity'));
//end of HEX to RGBA

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
        echo $categoryid;
		foreach ($results_cat as $count=>$result_cat)
		{       if($result_cat->id == $id){
                $cat_params = json_decode($result_cat->params);

                $choosed_image = $cat_params->image;

				$title = $result_cat->title;
				$text = $result_cat->description;
		}

		}

}
if ($input->getCmd('option') == 'com_content' && $input->getCmd('view') == 'article')
{
		$article_id = $article->id;
		$catid = $article->catid;
		$images = $article->images;
		$title = $article->title;
		$introtext = $article->introtext;
		$fulltext = $article->fulltext;
        $text =  $introtext.$fulltext;
$allimages = (json_decode($images));
$custom_image = $params->get('custom_image');
preg_match('/(?<!_)src=([\'"])?(.*?)\\1/', $text, $matches);
$article_body_image = $matches[2];
if (($params->get('image_in_the_spot') == 'intro_image') and ($allimages->image_intro) and ($is_category == 'false'))
{
		$choosed_image = $allimages->image_intro;
}
if (($params->get('image_in_the_spot') == 'full_image') and ($allimages->image_fulltext) and ($is_category == 'false'))
{
		$choosed_image = $allimages->image_fulltext;
}
if (($params->get('image_in_the_spot') == 'custom_image') and ($custom_image) and ($is_category == 'false'))
{
		$choosed_image = $params->get('custom_image');
}
if (($params->get('image_in_the_spot') == 'article_image') and ($article_body_image) and ($is_category == 'false'))
{
		$choosed_image = $article_body_image;
}
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
				if ($count_value == $field->id)
				{
						if (($field->title == $params->get('custom_field_image') and ($params->get('image_in_the_spot') == 'custom_field_image')))
						{
								$choosed_image = $field_value;
						}
						if (($field->title == $params->get('custom_field_title') and ($params->get('show_title') == 'title_from_custom_field')))
						{
								$title = $field_value;
						}

						if (($field->title == $params->get('custom_field_text') and ($params->get('show_text') == 'text_from_custom_field')))
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
if ((($maxLimit > 0) and ($params->get('show_text') == 'text_from_article') and ($is_category == 'false')))
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
if ($params->get('background_or_src') == 'src')
{
		$image_spot = '<img src="' . $thumbsnippet . $choosed_image . '" alt="' . $title . '" />';
}
?>

<?php

if ($params->get('show_in_articles') == '0' and ($is_category == 'false'))
{
		$disable_spot = 'true';
}
elseif ($params->get('show_in_categories') == '0' and ($is_category == 'true'))
{
		$disable_spot = 'true';
}
else{
    $disable_spot = 'false';
}

if ($choosed_image == ''){
   $disable_spot = 'true';
}
if (($choosed_image == '') and ($params->get('image_in_the_spot') == 'none')){
   $disable_spot = 'false';
}
if (($choosed_image == '') and ($params->get('image_in_the_spot') != 'none') and ($is_category == 'true')){
   $disable_spot = 'false';
}

?>

<?php if ($disable_spot == 'false') : ?>

<?php if ($input->getCmd('option') == 'com_content' && ($input->getCmd('view') == 'article') or ($is_category == 'true')) : ?>
<?php

if (($params->get('background_or_src') == 'src') and ($choosed_image == '')){
  $height = 'auto';
}
?>
<div id="spot_<?php echo $module->id; ?>" class="image_spot" style="height: <?php echo $height; ?>;<?php echo $background_spot; ?>">
     <?php if (($params->get('show_title') != 'none') or ($params->get('show_text') != 'none') or ((($params->get('show_cat_title') == '1') and ($is_category == 'true')) or (($params->get('show_cat_text') == '1') and ($is_category == 'true')))) : ?>
     <div class="info_spot" style="background-color: <?php echo $rgba;?>; color: <?php echo $rgba_text;?>">
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
     </div>
      <?php endif; ?>

     <?php if (($params->get('background_or_src') == 'src')  and ($params->get('image_in_the_spot') != 'none') and ($choosed_image != '')) : ?>
     <div class="src_image_spot">
         <?php echo $image_spot; ?>

      </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php endif; ?>
