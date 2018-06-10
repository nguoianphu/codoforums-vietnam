<?php

/*
 * @CODOLICENSE
 */


require LOCALE . '/' . LOCALE . '.php';

function _t($str, $plural = null, $count = 0) {

    if ($plural != null && $count > 1) {

        //return plural translation
        return codo_get_translation($plural, $count);
    }


    //return singular translation
    return codo_get_translation($str, $count);
}

//removes a translation from language file
function _r($index) {

    global $CODOT;

    unset($CODOT[$index]);
    write_to_file($CODOT);
}

function codo_get_translation($index, $count) {

    global $CODOT;

    if (!isset($CODOT[$index])) {

        $CODOT[$index] = $index;

        //add translation if does not exist
        if (MODE == 'DEVELOPMENT') {

            asort($CODOT);
            write_to_file($CODOT);
        }
    }
    
    return str_replace("%s", $count, $CODOT[$index]);
}

function write_to_file($arr) {

    $pre = "/**
 * 
 * Creator: 
 * 
 * Translation in codoforum is very simple 
 * Copy paste this file into
 * locale/your_language/your_language.php
 * 
 * For eg. locale/ru_RU/ru_RU.php or locale/russian/russian.php
 * 
 * After that , write translations of left of => to the right of =>
 * in that file.
 * 
 * For eg.
 * 
 * 'My profile' => 'Мой профиль',
 * 
 * You can then select the language from the backend
 *
 */
";

    file_put_contents(DATA_PATH . 'locale/' . LOCALE . '/' . LOCALE . '.php', "<?php\n\n$pre\n \$CODOT = " . var_export($arr, true) . ";");
}

/**
 * 
 * CODOFORUM no longer uses gettext() due to implementation time 
 * required for certain features. 
 */

/*
if (!function_exists("gettext")) {

    \CODOF\Lang\Lang::init();
    
    function gettext($str, $index = -1) {

        return \CODOF\Lang\Lang::gettext($str, $index);
    }

    function _t($str) {

		return gettext($str);
    }

    function _t($singular, $plural, $no) {

        return gettext($singular, $no);
    }

}else{

setlocale(LC_ALL, LOCALE);
putenv('LANGUAGE='.LOCALE);

$domain = "messages";
bindtextdomain($domain, DATA_PATH . "locale");
//bind_textdomain_codeset($domain, 'UTF-8');

textdomain($domain);
}
 <?php

 

 $CODOT = array (
  'My profile' => 'My profile',
  'Logout' => 'Logout',
  'Register' => 'Register',
  'Login' => 'Login',
  'You do not have enough permissions to view this page!' => 'You do not have enough permissions to view this page!',
  'Search in ' => 'Search in ',
  'Advanced search' => 'Advanced search',
  'Search in Topic titles' => 'Search in Topic titles',
  'Yes' => 'Yes',
  'No' => 'No',
  'Sort results by' => 'Sort results by',
  'Author' => 'Author',
  'Post created' => 'Post created',
  'No. of replies' => 'No. of replies',
  'No. of views' => 'No. of views',
  'Last post time' => 'Last post time',
  'Post body' => 'Post body',
  'Post title' => 'Post title',
  'Asc' => 'Asc',
  'Desc' => 'Desc',
  'Search within' => 'Search within',
  'Any time' => 'Any time',
  'Past hour' => 'Past hour',
  'Past 24 hours' => 'Past 24 hours',
  'Past week' => 'Past week',
  'Past month' => 'Past month',
  'Past year' => 'Past year',
  'Search' => 'Search',
  'Create Topic' => 'Create Topic',
  'Give a title for your topic' => 'Give a title for your topic',
  'Create new topic' => 'Create new topic',
  'Describe your topic . You can use BBcode or Markdown' => 'Describe your topic . You can use BBcode or Markdown',
  'live preview' => 'live preview',
  'Post' => 'Post',
  'Cancel' => 'Cancel',
  'enter atleast ' => 'enter atleast ',
  ' characters' => ' characters',
  'Topics' => 'Topics',
  'topics' => 'topics',
  'Posts' => 'Posts',
  'posts' => 'posts',
  'sub-category' => 'sub-category',
  'sub-categories' => 'sub-categories',
  'No posts to display' => 'No posts to display',
  'posted ' => 'posted ',
  'read more' => 'read more',
  'replies' => 'replies',
  'views' => 'views',
  'recent by' => 'recent by',
  'All posts under this topic will be ' => 'All posts under this topic will be ',
  'deleted' => 'deleted',
  'Delete' => 'Delete',
  'Drop files to upload &nbsp;&nbsp;(or click)' => 'Drop files to upload &nbsp;&nbsp;(or click)',
  'Add link' => 'Add link',
  'link url' => 'link url',
  'link text' => 'link text',
  'optional' => 'optional',
  'link title' => 'link title',
  'Add' => 'Add',
  'Upload' => 'Upload',
  'New topic' => 'New topic',
  'Title' => 'Title',
  'Category' => 'Category',
  'Select a category' => 'Select a category',
  'Edit' => 'Edit',
  'Search result' => 'Search result',
  'All topics' => 'All topics',
  'Search in forum' => 'Search in forum',
  'Search sub-categories' => 'Search sub-categories',
  'No topics created yet!' => 'No topics created yet!',
  'Be the first to ' => 'Be the first to ',
  'create one' => 'create one',
  'No more topics to display!' => 'No more topics to display!',
  'No topics found matching your criteria!' => 'No topics found matching your criteria!',
  'posted' => 'posted',
  'reply' => 'reply',
  'Start typing here . You can use BBcode or Markdown' => 'Start typing here . You can use BBcode or Markdown',
  'The post has been ' => 'The post has been ',
  'undo' => 'undo',
  'The page you are looking for does not exists!' => 'The page you are looking for does not exists!',
  'There was some error. Please check your confirmation link' => 'There was some error. Please check your confirmation link',
  'Email confirmation successfull' => 'Email confirmation successfull',
  'You will be redirected to your ' => 'You will be redirected to your ',
  'profile' => 'profile',
  ' in 2 seconds' => ' in 2 seconds',
  'User login' => 'User login',
  'username or e-mail address' => 'username or e-mail address',
  'E-mail new password' => 'E-mail new password',
  'Please fill both the fields!' => 'Please fill both the fields!',
  'username' => 'username',
  'password' => 'password',
  ' Keep me logged in' => ' Keep me logged in',
  'I forgot my password' => 'I forgot my password',
  'user profile edits saved successfully' => 'user profile edits saved successfully',
  'display name' => 'display name',
  'avatar' => 'avatar',
  'Change' => 'Change',
  'signature' => 'signature',
  'Save edits' => 'Save edits',
  'Current password' => 'Current password',
  'New password' => 'New password',
  'Confirm password' => 'Confirm password',
  'Change password' => 'Change password',
  'passwords do not match!' => 'passwords do not match!',
  'A confirmation email has been sent to your email address!' => 'A confirmation email has been sent to your email address!',
  'You have not yet confirmed your email address.' => 'You have not yet confirmed your email address.',
  'Resend email' => 'Resend email',
  'Joined' => 'Joined',
  'Last login' => 'Last login',
  'email' => 'email',
  'Already registered?' => 'Already registered?',
  'Login here' => 'Login here',
  'username cannot be less than ' => 'username cannot be less than ',
  'username already exists' => 'username already exists',
  'passowrd cannot be less than ' => 'passowrd cannot be less than ',
  'mail already exists' => 'mail already exists',
  'User does not exist with the given username/mail' => 'User does not exist with the given username/mail',
  'Unable to reset password' => 'Unable to reset password',
  'You must be logged in to reply' => 'You must be logged in to reply',
  'You do not have permission to ' => 'You do not have permission to ',
  'create a topic' => 'create a topic',
  'edit this topic' => 'edit this topic',
  'Edit topic ' => 'Edit topic ',
  'Create topic' => 'Create topic',
  'Access denied' => 'Access denied',
  'Request new passsword' => 'Request new passsword',
  'Access Denied' => 'Access Denied',
  'Not found' => 'Not found',
  'password cannot be greater than 72 characters!' => 'password cannot be greater than 72 characters!',
  'username can have only letters digits and underscores' => 'username can have only letters digits and underscores',
  'user already exists' => 'user already exists',
  'email address not formatted correctly' => 'email address not formatted correctly',
  'email address is already registered' => 'email address is already registered',
  'username field cannot be left empty' => 'username field cannot be left empty',
  'password field cannot be left empty' => 'password field cannot be left empty',
  'until ' => 'until ',
  'You have been banned ' => 'You have been banned ',
  'Wrong username or password' => 'Wrong username or password',
  'capcha entered was wrong' => 'capcha entered was wrong',
  'ago' => 'ago',
  'at' => 'at',
  'just now' => 'just now',
  'second' => 'second',
  'seconds' => 'seconds',
  'minute' => 'minute',
  'minutes' => 'minutes',
  'hour' => 'hour',
  'hours' => 'hours',
  'Today' => 'Today',
  'day' => 'day',
  'days' => 'days',
  'Be notified of new replies' => 'Be notified of new replies',
  'Password updated successfully' => 'Password updated successfully',
  'The current password given is incorrect' => 'The current password given is incorrect',
);
*/