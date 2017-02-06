<?php



defined('_JEXEC') or die;





$input=Jfactory::getApplication()->input;

if($input->getCmd('option')=='com_content' 

&& $input->getCmd('view')=='article' ){

  $db=JFactory::getDbo();

  $db->setQuery('select catid from #__content where id='.$input->getInt('id')); 

  $catid=$db->loadResult(); 

$db->setQuery('select images from #__content where id='.$input->getInt('id')); 

  $images=$db->loadResult();

}



$theimage =(json_decode($images));

$custom_image = $params->get('custom_image');

echo '<div class="image_spot" style="height:'.$params->get('height').'">';

if (($params->get('image_in_the_spot') == 'intro_image') and (!empty($theimage->image_intro))){

echo  '<img src="'.$theimage->image_intro.'" />';

}

if (($params->get('image_in_the_spot') == 'full_image') and (!empty($theimage->image_fulltext))){

echo  '<img src="'.$theimage->image_fulltext.'" />';

}

if (($params->get('image_in_the_spot') == 'custom_image') and (!empty($custom_image))){

echo  '<img src="'.$params->get('custom_image').'" />';

}

echo '</div>';
