<?php
/*
 * Copyright 2013 Benjamin Roy
 * This program is distributed under the terms of the GNU General Public License version 2.0
 *
 * Program name: Ben's Picture Gallery (BPG)
 * Web site: https://github.com/benroy73/bpg
 * Author: Benjamin Roy, email: benroy@7373.us
 * License: GPLv2
 * Version Release Date: May 2013
 * Version: 3.0
 *
 */

// ---------------------------------------------------------------------
// Customize these variables for your own site in your config.php file
// The config.php file will override these values.
// ---------------------------------------------------------------------

// The real path on the server to the original photos
// it can be relative to this bpg.php script or absolute.
$original_photos_dir = 'photos'; // (no trailing slash)

// The name of your gallery
$gallery_name = 'Picture Gallery';

// The name to put in the copyright tag of the RSS feeds
$copyright_owner_name = '';

// The font to use for watermarking images
$watermark_font_file  = "images/FreeSansBoldOblique.ttf";  // you can change this to any TrueType font file
$watermark_font_size  = 32;

// The name to use for the top level breadcrumb and the RSS feed.
// It will be linked to the parent directory of this script.
$home_site_name = 'My Website';

// Sort the pictures in the browser by filename or EXIF Date
$sort_style = 'file_mtime';  // filename or file_mtime

// The bits per second for your outgoing bandwith.  Used to calculate downloads from the cart.
$upstream_bandwidth_bits = 1000000;

// The user that the web server runs as.  www-data on a Debian or Ubuntu system.
// This is used to try to make sure file permissions get set properly in the cache.
$webserver_user = 'www-data';

// The location of the cached files, it needs to be writable by the webserver and browsable by visitors.
// It's location is relative to this script and is where all the generated files are stored.
// If PHP can't create this directory you will need to create it manually like "mkdir _cache; chmod 777 _cache;"
$cache_dir = '_cache'; //  (no trailing slash)

// What size should the pictures be resized to?
$thumbnail_image_dimension  = '160';
$small_image_dimension      = '640';
$medium_image_dimension     = '1280';
$large_image_dimension      = '1920';

// How good should the resized pictures look on a scall from 1-100 where 100 is perfect and 1 is terrible
// the jpeg compression quality 0-100 (75 seems like a good balance)
$jpeg_image_quality = '75';

// What features should be enabled?
$enable_ffmpeg_video_feature    = TRUE;
$enable_exif_editing_feature    = TRUE;
$enable_site_stats_feature      = TRUE;  // display site statistics on the top page
$enable_zip_download_feature    = TRUE;
$enable_buy_prints_feature      = TRUE;
$enable_load_balancing_feature  = FALSE;
$enable_watermark_feature       = FALSE;


// External commands that this program depends on. They must be excutable by the web server.
$exiftool_cmd         = "/usr/bin/exiftool"; // this program writes the Exif metadata
$zip_cmd              = '/usr/bin/zip'; // this is used to make a downloadable zip file from the files in the cart
$id3v2_cmd            = '/usr/bin/id3v2';  // shell command to use if PHP is missing the id3_get_tag function
$echo_cmd             = 'echo';  // shell command used for statistics
$grep_cmd             = 'grep';  // shell command used for statistics
$du_cmd               = 'du';  // shell command used for statistics
$find_cmd             = 'find';  // shell command used for statistics
$wc_cmd               = 'wc';  // shell command used for statistics
$awk_cmd              = 'awk';  // shell command used for statistics

// instructions to install ffmpeg on Ubuntu http://ubuntuforums.org/showthread.php?t=786095
$ffmpeg_cmd           = '/usr/local/bin/ffmpeg';  // this converts videos to the H.264 MP4 format
$ffmpeg_metadata_cmd  = '/usr/local/bin/qt-faststart'; // command for adding metadata to mp4 file
// on a Mac ffmpeg is often located at /opt/local/bin/ffmpeg

// The URL where the RSS summary announcement feed will be available publicly
// RSS readers like Google Reader can subscribe to this to see when new photos are added
$photo_public_rss_url = '/photos/rss.xml';
/*
If your site is password protected and you want visitors to be able to get updates in a RSS reader, then
make Apache serve the rss.xml link without restrictions like this
    RewriteEngine On
    RewriteRule ^/photos/rss.xml$ /photos/index.php?view=rsspub
    <Location /photos/rss.xml>
        Allow from all
    </Location>
*/

// URL that photo lab will use to access the copies of the original photos for printing.
// Original files are temporarily copied to ./$cache_dir/_orders/ when buying prints,
// this URL needs to be accessible to the lab's servers without authentication.
$print_lab_orders_url = 'http://localhost/print-orders'; // (no trailing slash)


// To edit jpeg comments or delete the original files the user name must be in this $admin_users array
// (the original files must be writable by the webserver for this to work).
// The user name is identified by the by the web server's basic auth (.htaccess), so you'll need to use
// Apache's htpasswd and .htaccess files to make these users login
$admin_users = array(); // example: array( 'alice', 'bob' )


// the list of sites to use for load balancing
$load_balance_hosts = array( 'http://localhost/photos1', 'http://localhost/photos2' );

// what subnet is local and fast and should get higher quality videos
// usually this is '192.168.'
$local_subnet = '192.168.';

// The google analytics id code to use, if you want to enable it
//$google_analytics_tracking_code = '';

// The URL to the AWStats js file, if you want to enable it
//$awstats_url = '';




// =============================================================================
//
// Don't change anything below here unless you know what you are doing
//
// =============================================================================

// override the variables above so settings can be saved between upgrades in a local config.php file
if ( file_exists('config.php') ) include('config.php');


$google_analytics_js = '';
if (isset($google_analytics_tracking_code)) {
    $google_analytics_js = "
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', '$google_analytics_tracking_code']);
      _gaq.push(['_trackPageview']);

      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();
    ";

}

$awstats_js = '';
if (isset($awstats_url)) {
    $awstats_js = "
      (function() {
        var node = document.createElement('script'); node.type = 'text/javascript'; node.async = true;
        node.src = '$awstats_url';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(node, s);
      })();
    ";
}


$html_page_top = <<<"EOD"
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>$gallery_name</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Photo gallery">
    <meta name="author" content="$copyright_owner_name">

    <!-- CSS -->
    <link type='text/css' rel='stylesheet' href="bootstrap/css/bootstrap.css"/>
    <link type='text/css' rel='stylesheet' href="bootstrap/css/bootstrap-responsive.css"/>

    <link type='text/css' rel='stylesheet' href='photoswipe-3.0.5.1/photoswipe.css'/>
    <link type='text/css' rel='stylesheet' href='mediaelement-2.10.3-benroy73/mediaelementplayer.css'/>
    <link type='text/css' rel='stylesheet' href='bpg.css'/>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements https://code.google.com/p/html5shiv/ -->
    <!--[if lt IE 9]>
      <script type='text/javascript' src="bootstrap/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/img/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/img/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed"   sizes="72x72" href="/img/apple-touch-icon-72-precomposed.png">
                    <link rel="apple-touch-icon-precomposed" href="/img/apple-touch-icon-57-precomposed.png">
    <link rel="icon" type="image/png" href="/img/favicon.png">

    <link type='application/rss+xml' rel='alternate' href='$photo_public_rss_url' title='announcements of new photos'/>
    <link type='application/rss+xml' rel='alternate' href='bpg-srv?view=rssnew' title='latest photo folder'/>

  </head>
  <body>

    <!-- Part 1: Wrap all page content here -->
    <div id="wrap">

      <!-- Fixed navbar -->
      <div class="navbar navbar-fixed-top">
        <div class="navbar-inner">
          <div class="container">
            <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="brand" href="/">$home_site_name</a>
            <div class="nav-collapse collapse">
              <ul class="nav">
                <li id="top_level_breadcrumb"><a href="?">Photos</a></li>
                <!--<li class="divider-vertical"></li>-->
              </ul>
              <ul class="nav pull-right">
                <li class="pull-right" id="ui_admin_button"><a id="admin_mode_button" href="#">Admin</a></li>
                <li class="dropdown pull-right" id="ui_cart_menu">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-shopping-cart"></i> Cart <b class="caret"></b></a>
                  <ul class="dropdown-menu">
                    <li><a id="add_some_to_cart" href="#">Add some pictures</a></li>
                    <li><a id="add_all_to_cart" href="#">Add all pictures</a></li>
                    <li><a id="remove_some_from_cart" href="#">Remove some pictures</a></li>
                    <li><a id="remove_all_from_cart" href="#">Empty cart</a></li>
                    <li class="divider"></li>
                    <li><a id="view_cart_menu_option" href="?dir=cart">View cart</a></li>
                    <li><a id="buy_prints_menu_option" href="#">Buy Prints</a></li>
                    <li><a id="download_files_menu_option" href="#">Download Files</a></li>
                  </ul>
                </li>
              </ul>
            </div><!--/.nav-collapse -->
          </div>
        </div>
      </div>

      <!-- Begin page content -->
      <div id="content_media" class="container">

EOD;

$html_page_bottom = <<<"EOD"

      </div>

      <div id="push"></div>
    </div>

    <div id="footer">
      <div class="container">
        <p id='muted credit'>Powered by <a href='https://github.com/benroy73/bpg'>BPG</a> - <span id='site_stats'></span></p>
      </div>
    </div>

    <div id="videoPlayerModal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
            <div id="slideshow_screen"></div>
        </div>
    </div>

    <!-- Placed at the end of the document so the pages load faster -->
    <script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js'></script>
    <script type='text/javascript' src="bootstrap/js/bootstrap.min.js"></script>
    <script type='text/javascript' src='mediaelement-2.10.3-benroy73/mediaelement-and-player.min.js'></script>
    <script type='text/javascript' src='photoswipe-3.0.5.1/lib/klass.min.js'></script>
    <script type='text/javascript' src='photoswipe-3.0.5.1/code.photoswipe.jquery-3.0.5.1.min.js'></script>
    <script type='text/javascript' src='jquery.jeditable.js'></script>
    <script type='text/javascript' src='bpg.js'></script>

    <script type='text/javascript'>
      $google_analytics_js
      $awstats_js
    </script>

  </body>
</html>
EOD;


// =============================================================================
//
// Really don't mess with stuff below here unless you understand what you are doing
//
// =============================================================================


// disable load balancing within the LAN
if ( isset($_SERVER['REMOTE_ADDR']) && strpos($_SERVER['REMOTE_ADDR'], $local_subnet) !== false ) {
    $enable_load_balancing_feature = FALSE;
}

ini_set('session.cookie_httponly', 1); // override the default setting and don't let javascript used this cookie
session_start(); // use PHP session to track users shopping cart
if (isset($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
}

$balance_iterator = 0; //placeholder for which load balancing host to use next

function isAdminUser() {  // decide if the current user has admin privileges
    global $admin_users;
    if ( isset($_SERVER["PHP_AUTH_USER"]) ) {
        return in_array($_SERVER["PHP_AUTH_USER"], $admin_users);
    }
    else {
        return FALSE;
    }
}

function getUiSettings() {
    global $enable_zip_download_feature, $enable_exif_editing_feature, $enable_buy_prints_feature;
    $ui_settings['isAdmin'] = isAdminUser();
    $ui_settings['download_enabled'] = $enable_zip_download_feature;
    $ui_settings['exif_editing_enabled'] = $enable_exif_editing_feature;
    $ui_settings['buy_prints_enabled'] = $enable_buy_prints_feature;

    return $ui_settings;
}

function ajaxError( $msg ) {
    header("HTTP/1.1 500 Internal Server Error");
    print $msg;
    exit(1);
}

function ajaxJsonResponse( $obj ) {
    header('Content-Type: application/json');
    print json_encode($obj);
    exit(0);
}

if (!function_exists('id3_get_tag')) {  // use the id3v2 command if PHP is missing this function
    function id3_get_tag( $filepath ) {
        global $id3v2_cmd;
        if (!is_executable($id3v2_cmd) && is_executable('/opt/local/bin/id3v2')) { // try the Mac location
            $id3v2_cmd = '/opt/local/bin/id3v2';
        }
        $tags['comments'] = exec("$id3v2_cmd -l \"$filepath\" |grep '^COMM (Comments): (Recording notes'|awk -F': ' '{print \$3}' ");
        $tags['artist'] = exec("$id3v2_cmd -l \"$filepath\" |grep '^TPE1'|awk -F': ' '{print \$2}'");
        return $tags;
    }
}

function handled_filetype( $file ) { //return the type of file if it is supported, or false if unsupported
    $type = FALSE;
    $extension = strtolower( substr($file, -4, 4) );
    switch ($extension) {
        case '.jpg':
            $type = 'photo';
            break;
        case 'jpeg':
            $type = 'photo';
            break;
        case '.avi':
            $type = 'video';
            break;
        case '.mov':
            $type = 'video';
            break;
        case '.mp4':
            $type = 'video';
            break;
        case '.3gp':
            $type = 'video';
            break;
        case '.mp3':
            $type = 'audio';
            break;
        case '.wav':
            $type = 'audio';
            break;
    }
    return $type;
}

function safe_path( $path ) {  // scrub a path provided by the browser to make it safe
/* data provided by a user can't be trusted so we need to check it here
   the rules we enforce here are
    1. no '..' allowed in path
    2. don't start with /
*/
    $path = stripslashes (urldecode( stripslashes($path) ));
    $path = trim ( $path, '/\\' );

    if ( strpos($path, '..') !== false ) {
        $path = '';  // don't allow ..
        print "don't mess with the dir parameter!\n";
    }
    if ( substr($path, 0, 1) == '/' ) {
        $path = ''; // don't start with /
        print "Don't mess with the dir parameter!\n";
    }
    return $path;
}

function urlencode_path( $string ) { // encode a file path so it can be used in URLs
    $result = implode("/", array_map("rawurlencode", explode("/", $string)));
    $result = str_replace(' ', '%20', $result);
    return $result;
}

function file_mtime_sorter($a, $b) {
    global $current_path;
    $a_filepath = "$current_path/$a";
    $b_filepath = "$current_path/$b";

    $a_time = filemtime( $a_filepath );
    $b_time = filemtime( $b_filepath );

    if ($a_time == $b_time) {
        return 0;
    }
    else {
        return ($a_time < $b_time) ? -1 : 1;
    }
}

function get_files_and_dirs( $path, $only_handled_filetypes=TRUE ) { // get all the files and folders in the path requested
    global $cache_dir, $sort_style, $current_path;
    $files = array();
    $dirs = array();
    $files_and_dirs = array();

    if ($path == '' || !$path) $path = '.'; // don't allow an empty path

    if (is_dir($path)) {
        $files = scandir( $path ); // get the directory list for a path
    }

    if (is_array($files)) {
        foreach( $files as $i => $file ) { // separate the file and dirs
            if (substr($file, 0, 1)==".") { // ignore files and dirs that start with '.'
                unset( $files[$i] );
            }
            elseif (is_dir("$path/$file")) { // dirs
                if ( $file != $cache_dir ) $dirs[] = $file ;
                unset( $files[$i] );
            }
            elseif ($only_handled_filetypes && (!handled_filetype($file))) {
                // we only want handled file types, and this isn't one of them
                unset( $files[$i] );
            }
        }
    }

    $current_path = $path; // set this global variable for use in the sort function
    //default sort method is alphabetic by filename
    if ($sort_style == "file_mtime") {
        usort($files, "file_mtime_sorter");
    }

    rsort($dirs);
    $files_and_dirs['dirs'] = $dirs;
    $files_and_dirs['files'] = $files;
    return $files_and_dirs;
}

function get_files_grouped_by_type($files) {
    $files_by_type = array();
    $files_by_type['photo'] = array();
    $files_by_type['video'] = array();
    $files_by_type['audio'] = array();
    foreach( $files as $file ) {
        if (handled_filetype($file)) {
            $files_by_type[handled_filetype($file)][] = $file;
        }
    }
    return $files_by_type;
}

function mkdir_r($dir_name, $rights=0777){ // take a path and make all the directories if they don't exist yet
    $dirs = explode('/', $dir_name);
    $dir = '';
    $blank_html_page =
"<!DOCTYPE html>
<html lang='en'>
  <head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>blank page</title>
  </head>
  <body>
    <div style='text-align: center; margin-top: 20%;'>This page is intentionally blank.</div>
  </body>
</html>
";
    foreach ($dirs as $part) {
        $dir .= $part . '/';
        if (!is_dir($dir) && strlen($dir)>0) {
            mkdir($dir, $rights);
            chmod($dir, 0777);
        }
        if (!is_file($dir ."index.html")) {
            file_put_contents($dir ."index.html", $blank_html_page);
        }
    }
}

function makeScaled($im, $size, $new_size, $rotation=0) { // resize the image keeping the same aspect ratio
    $width = $size[0];
    $height = $size[1];

    if ($width<=$new_size && $height<=$new_size) return $im;

    if ($width>$height) { //fat images, normal landscape format
            $ratio = $width/$new_size;
            $newWidth=$new_size;
            $newHeight = round($height/$ratio,0);
    }
    elseif ($width<$height) {  //tall images, portrait format
            $ratio = $height/$new_size;
            $newHeight = $new_size;
            $newWidth= round($width/$ratio,0);
    }
    else { //a square image
            $newWidth = $new_size;
            $newHeight = $new_size;
    }

    //make the new image
    $destImage = ImageCreateTrueColor( $newWidth, $newHeight);

    //copy the passed image into the new image at the proper scale
    // Resized is about 30% faster, Resampled is better quality
    ImageCopyResized( $destImage, $im, 0, 0, 0, 0, $newWidth+1, $newHeight+1, $width, $height );
    //  // Resampled is better quality but about 30% slower
    //  ImageCopyResampled( $destImage, $im, 0, 0, 0, 0, $newWidth+1, $newHeight+1, $width, $height );
    //
    // ImageMagik is about 10% slower than PHP/GD Resized
    //exec("/usr/bin/convert -size 800x800 \"$filepath\" -resize 800x800 \"$cached_mediumfile\"");
    //if ($rotation > 0)
    //  exec("/usr/bin/convert -rotate $rotation \"$cached_mediumfile\" \"$cached_mediumfile\"");
    //exec("/usr/bin/convert -size 160x160 \"$cached_mediumfile\" -resize 160x160 \"$cached_thumbfile\"");

    if ($rotation>0) {
        $color = ImageColorAllocate($destImage,255,255,255);
        $destImage = ImageRotate($destImage,$rotation,$color);
    }

    return $destImage;
}


function get_original_path( $cache_file ) {
    // this might help with videos in the cart
}

function get_cache_path( $filepath, $size='_thumbnails' ) {
    // get the path for a _large, _medium, _small or _thumbnail images in the cache
    global $cache_dir, $original_photos_dir;
    $file = basename($filepath);
    $dir = dirname($filepath);

    if ($size == 'original' ) {
        $path = "$original_photos_dir/$dir/$file";
    }
    elseif (handled_filetype($filepath) == 'audio' && $size == '_thumbnails') {
        $path = "images/sound-icon.png";
    }
    else {
        $path = "$cache_dir/$size/$dir/$file";
    }

    if ( handled_filetype($filepath) == 'video' ) {
        if ($size == '_thumbnails') {
            $path = substr_replace($path, '.jpg', -4);
        }
        else {
            $path = substr_replace($path, '.mp4', -4);
        }
    }

    return $path;
}

function get_load_balanced_url( $filepath ) { // returns a load balanced URL
    global $load_balance_hosts, $balance_iterator;
    $filepath = urlencode_path($filepath);

    if (load_balancing_feature_enabled()) {
        $mirror_url = $load_balance_hosts[$balance_iterator] .'/'. $filepath;
        $balance_iterator++;
        if ($balance_iterator > count($load_balance_hosts)-1) {
            $balance_iterator = 0;
        }
        return $mirror_url;
    }
    else {
        return $filepath;
    }
}


function generate_cached_audio_files( $filepath ) {
    global $original_photos_dir;
    $original_file = "$original_photos_dir/$filepath";
    copy($original_file, get_cache_path( $filepath, '_large' ));
    copy($original_file, get_cache_path( $filepath, '_medium' ));
    copy($original_file, get_cache_path( $filepath, '_small' ));
    // thumbnails are all directed to images/sound-icon.png in get_cache_path function so I don't need to create it here
}

function get_video_dimension( $filepath ) {
    global $ffmpeg_cmd, $grep_cmd, $awk_cmd;
    $forig_es = escapeshellarg($filepath);

        //    # get the video size
        //    #    Stream #0.0: Video: mjpeg, yuvj422p, 640x480, 15 tbr, 15 tbn, 15 tbc
        //    #    Stream #0.0(eng): Video: h264, yuvj420p, 1280x720, 23182 kb/s, 30 fps, 30 tbr, 3k tbn, 6k tbc
    $exec_cmd = "$ffmpeg_cmd -i $forig_es -vstats 2>&1 |$grep_cmd 'Stream.*Video' |$awk_cmd -F, '{print $3}' |$awk_cmd '{print $1}'";
    $video_dimensions = '';
    $video_dimensions = trim(exec($exec_cmd));
    //error_log( $exec_cmd . "\n" . $video_dimensions );
    return $video_dimensions;
}

function generate_cached_video_files( $filepath, $overwrite_existing_files=TRUE ) {
    global $original_photos_dir, $jpeg_image_quality, $thumbnail_image_dimension;
    global $ffmpeg_cmd, $grep_cmd, $awk_cmd;

    $cached_thumbfile  = get_cache_path( $filepath, '_thumbnails' );
    $cached_smallfile  = get_cache_path( $filepath, '_small' );
    $cached_mediumfile = get_cache_path( $filepath, '_medium' );
    $cached_largefile  = get_cache_path( $filepath, '_large' );

    if ( ! ffmpeg_video_feature_enabled() ) {
        return FALSE;
    }

    $filepath = "$original_photos_dir/$filepath";
    $forig_es = escapeshellarg($filepath);

    if (!is_file($cached_thumbfile) || $overwrite_existing_files) {
        // use the camera's thm files for movies if possible
        // check for lower or upper case file names
        if (is_file( substr_replace($filepath, '.thm', -4))) {
            $thmfile = substr_replace($filepath, '.thm', -4);
        }
        elseif (is_file( substr_replace($filepath, '.THM', -4))) {
            $thmfile = substr_replace($filepath, '.THM', -4);
        }
        else {
            // no .THM file so need to use ffmpeg to get tumbnail image
            //ffmpeg -y -i MVI_6640.AVI -s qcif -f mjpeg -t 0.001 movie.jpg
            $tempfile = $cached_thumbfile . '.temp.jpg';
            $tempfile_es = escapeshellarg($tempfile);
//            exec("$ffmpeg_cmd -y -v 0 -i $forig_es -s 160x120 -f mjpeg -t 0.001 $thmfile_es 2>&1");  // this does not work on all video files
//            exec("$ffmpeg_cmd -y -v 0 -i $forig_es -s 160x120 -ss 00:00:01.00 -vcodec mjpeg -vframes 1 ". escapeshellarg($cached_thumbfile) ." 2>&1");  // unfortunately this stretches the image proportions

            // get a full sized frame
            $exec_cmd = "$ffmpeg_cmd -y -v 0 -i $forig_es -ss 00:00:01.00 -vcodec mjpeg -vframes 1 $tempfile_es 2>&1";
            $last_error = exec($exec_cmd, $output, $retvar);
            if ( $retvar != 0 ) {
                error_log( $exec_cmd );
                error_log( $last_error );
                error_log( print_r(array_pop($output), true) );
            }
            // then resize it to the thumbnail size
            $im = ImageCreateFromJpeg($tempfile); // read the image into memory
            $size = array(imagesx($im), imagesy($im));
            $im = makeScaled($im, $size, $thumbnail_image_dimension); // scale the image in memory
            ImageJpeg($im, $cached_thumbfile, 90); // save the image file
            ImageDestroy($im); // release the image in memory
            unlink($tempfile); // delete the temp file

            $thmfile = $cached_thumbfile;
        }

        // add the filmstrip border to the thumbnail in the cache
        // just email yourself an image and it will be base64 encoded like this
        $image = 'iVBORw0KGgoAAAANSUhEUgAAABQAAAB4CAYAAADyv9IsAAAACXBIWXMAAAsT
    AAALEwEAmpwYAAAAYElEQVRo3u3UMQ7AIBADwXOU/3/Z+UAKCrobSiSmwNJm
    ZjoXzzOXz0Lw/btsm5PHSeoPgSvAyBdQYIFAgQUKrFGAAmsUgRVYIFBggQIL
    BAosUGCNAhRYowiswAKBWwP7AQXJJO+EVoJJAAAAAElFTkSuQmCC';
        $image = base64_decode($image);
        $filmstrip = imageCreateFromString($image);
        //$filmstrip = imageCreateFromPNG('images/filmstrip.png');

        $filmstrip_width = imagesx($filmstrip);
        $filmstrip_height = imagesy($filmstrip);
        $image = imageCreateFromJpeg($thmfile);
        $size = getimagesize($thmfile);
        $dest_x = $size[0] - $filmstrip_width;
        $dest_y = $size[1] - $filmstrip_height;
        imagecopymerge($image, $filmstrip, 0, 0, 0, 0, $filmstrip_width, $filmstrip_height, 50);
        imagecopymerge($image, $filmstrip, $dest_x, $dest_y, 0, 0, $filmstrip_width, $filmstrip_height, 50);
        ImageJpeg($image, $cached_thumbfile, $jpeg_image_quality);
        imagedestroy($image);
        imagedestroy($filmstrip);
    }

        // video encoding takes too long so only generate the video files when run from the command line
        // this should be run from cron periodically (perhaps hourly)
        // php bpg-dev.php -g -d "all" -t all
    if ( PHP_SAPI == 'cli' ) { // command line execution

        // http://developer.apple.com/safari/library/documentation/AudioVideo/Conceptual/Using_HTML5_Audio_Video/AudioandVideoTagBasics/AudioandVideoTagBasics.html#//apple_ref/doc/uid/TP40009523-CH2-SW1
        // https://developer.apple.com/library/safari/#documentation/AppleApplications/Reference/SafariWebContent/CreatingVideoforSafarioniPhone/CreatingVideoforSafarioniPhone.html

        // http://ubuntuforums.org/showthread.php?t=786095
        // http://rob.opendot.cl/index.php/useful-stuff/ffmpeg-x264-encoding-guide/
        // http://rob.opendot.cl/index.php/useful-stuff/ipod-video-guide/

        // http://ffmpeg.org/trac/ffmpeg/wiki/FilteringGuide
        // http://enddl22.net/wordpress/?p=2499

        // http://ffmpeg.org/trac/ffmpeg/ticket/309

        //    # get the video size
        //    #    Stream #0.0: Video: mjpeg, yuvj422p, 640x480, 15 tbr, 15 tbn, 15 tbc
        //    #    Stream #0.0(eng): Video: h264, yuvj420p, 1280x720, 23182 kb/s, 30 fps, 30 tbr, 3k tbn, 6k tbc
        //$exec_cmd = "$ffmpeg_cmd -i $forig_es -vstats 2>&1 |$grep_cmd Video |$awk_cmd -F, '{print $3}'";
        //$video_dimensions = '';
        //$video_dimensions = trim(exec($exec_cmd));
        ////error_log( $exec_cmd . "\n" . $video_dimensions );

        // adding  -preset slow after baseline would improve quality but make the encodeing much slower
        // iphone max is 640x480
        // ipad & iphone4 max is 1280x720
        if (!is_file($cached_smallfile) || $overwrite_existing_files) {
            //
            // generate the small size H.264 MP4 version of the file
            //
            $h264options = '-acodec libfaac -aq 100 -r 15 -vcodec libx264 -pix_fmt yuv420p -vprofile baseline -crf 31 -vf scale="320:trunc(ow/a/2)*2" -threads 0';
            run_ffmpeg_command($filepath, $cached_smallfile, $h264options);
        }
        if (!is_file("$cached_smallfile.jpg") || $overwrite_existing_files) {
            save_video_poster_jpg($cached_smallfile, 'small');
        }

        if (!is_file($cached_mediumfile) || $overwrite_existing_files) {
            //
            // generate the medium size H.264 MP4 version of the file
            //
            $h264options = '-acodec libfaac -aq 100 -r 15 -vcodec libx264 -pix_fmt yuv420p -vprofile baseline -crf 29 -vf scale="640:trunc(ow/a/2)*2" -threads 0';
            run_ffmpeg_command($filepath, $cached_mediumfile, $h264options);
        }
        if (!is_file("$cached_mediumfile.jpg") || $overwrite_existing_files) {
            save_video_poster_jpg($cached_mediumfile, 'medium');
        }

        if (!is_file($cached_largefile) || $overwrite_existing_files) {
            //
            // generate the large size H.264 version of the file
            //
            // baseline should allow more hardware decoders to play it
            $h264options = "-acodec libfaac -aq 150 -r 30 -vcodec libx264 -pix_fmt yuv420p -vprofile baseline -crf 28 -threads 0";
            run_ffmpeg_command($filepath, $cached_largefile, $h264options);
        }
        if (!is_file("$cached_largefile.jpg") || $overwrite_existing_files) {
            save_video_poster_jpg($cached_largefile, 'large');
        }
    }

/*
ffmpeg -y -i INPUT.MOV -acodec libfaac -ab 128k -ac 2 -s 480x270 -vcodec mpeg4 -b 378k -flags +aic+mv4+trell -mbd 2 -cmp 2 -subcmp 2 -g 250 -maxrate 512k -bufsize 2M output.mp4


works on ipod touch:
ffmpeg -y -i INPUT.MOV -acodec libfaac -ab 48k -ac 2 -s 480x270 -vcodec mpeg4 -b 378k -mbd 2 -cmp 2 -subcmp 2 -g 250 -maxrate 512k -bufsize 2M output.mp4


works on ipod touch:
ffmpeg -y -i INPUT.MOV \
-acodec libfaac -ab 48k -ac 2 \
-r 15 -s 480x270 -vcodec mpeg4 \
-flags +aic+mv4 -trellis 1 -mbd 2 -cmp 2 -subcmp 2 -g 250 -maxrate 512k -bufsize 2M -metadata title="test video" output.mp4


/usr/local/share/ffmpeg/libx264-ipod320.ffpreset
/usr/local/share/ffmpeg/libx264-ipod640.ffpreset


works on ipod touch:
ffmpeg -y -i INPUT.MOV -acodec libfaac -aq 100 -ac 2 -vcodec libx264 \
-vpre ipod640 -crf 30 -vf scale=640:-1 -threads 0 output.mp4

works on ipod touch:
ffmpeg -y -i INPUT.MOV -acodec libfaac -aq 100 -ac 2 -r 15 -vcodec libx264 \
-vpre ipod320 -crf 31 -vf scale=320:-1 -threads 0 output.mp4


ffmpeg -y -i INPUT.MOV -acodec libfaac -aq 100 -r 15 -vcodec libx264 -vpre ipod320 -crf 31 -vf scale=320:-1 -threads 0 output.mp4

ffmpeg -y -i INPUT.MOV -acodec libfaac -aq 100 -r 15 -vcodec libx264 -vpre ipod640 -crf 29 -vf scale=640:-1 -threads 0 output-medium.mp4

ffmpeg -y -i INPUT.MOV -acodec libfaac -aq 100 -vcodec libx264 -crf 28 -threads 0 output-high.mp4
ffmpeg -y -i INPUT.MOV -acodec libfaac -aq 100 -vcodec libx264 -vprofile baseline -crf 28 -threads 0 output-high-baseline.mp4

*/
}

function run_ffmpeg_command($original_file, $cache_file, $h264options) {
    global $ffmpeg_cmd, $ffmpeg_metadata_cmd;
    $forig_es = escapeshellarg($original_file);
    $ftemp    = substr_replace($cache_file, '.tmp.mp4', -4);
    $ftemp_es = escapeshellarg($ftemp);
    $fnew_es  = escapeshellarg($cache_file);

    $exec_cmd = "$ffmpeg_cmd -v 0 -y -i $forig_es $h264options $ftemp_es";
    //error_log( $exec_cmd );

    $last_error = exec($exec_cmd . " 2>&1", $output, $retvar);
    if ( $retvar != 0 ) {
        error_log( $exec_cmd );
        error_log( $last_error );
        error_log( print_r(array_pop($output), true) );
    }
    if ( !is_file( $ftemp ) ) {
        $out = print_r($output, true);
        print "<pre>ffmpeg failed to create file for cache ($cache_file).\n$exec_cmd\n$out</pre>";
        return;
    }

    if ( !is_executable($ffmpeg_metadata_cmd) && is_executable('/usr/local/bin/qtfaststart.py') ) {
        $ffmpeg_metadata_cmd = '/usr/local/bin/qtfaststart.py';
    }
    if ( is_executable($ffmpeg_metadata_cmd) ) {
        exec("$ffmpeg_metadata_cmd $ftemp_es $fnew_es 2>&1"); // add metadata for progressive download/fast-start
        unlink($ftemp);
    }
    else {
        rename($ftemp, $cache_file);
    }
}

function save_video_poster_jpg($cache_file, $size) {
    global $ffmpeg_cmd;
    $video_file = escapeshellarg($cache_file);
    $poster_file = escapeshellarg("$cache_file.jpg");

    list($x, $y) = explode('x', get_video_dimension( $cache_file ), 2);
    if ($x <= 150 || $y <= 150) {
        $size = 'small';
    }

    if ($size == 'small') {
        $button_file = 'images/play_button_small.png';
    }
    else if ($size == 'medium') {
        $button_file = 'images/play_button_medium.png';
    }
    else {
        $button_file = 'images/play_button.png';
    }

    $ffmpeg_filter_options = "-ss 00:00:01.00 -vcodec mjpeg -vframes 1 -f image2 -vf 'movie=$button_file [wm]; [in][wm] overlay=main_w/2-overlay_w/2:main_h/2-overlay_w/2 [out]'";

// http://www.idude.net/index.php/how-to-watermark-a-video-using-ffmpeg/

/*
/usr/local/bin/ffmpeg -y -i '_cache/_small/2005/test'\''s ! ~ weird chars/MVI_0019.mp4' -ss 00:00:01.00 -vcodec mjpeg -vframes 1 -f image2 -vf 'movie=images/play_button.png [wm]; [in][wm] overlay=main_w-overlay_w-10:main_h-overlay_h-10 [out]' '_cache/_small/2005/test'\''s ! ~ weird chars/MVI_0019.mp4'.jpg

/usr/local/bin/ffmpeg -y -i '_cache/_small/2005/test'\''s ! ~ weird chars/MVI_0019.mp4' -ss 00:00:01.00 -vcodec mjpeg -vframes 1 -f image2 -vf 'movie=images/play_button_small.png [wm]; [in][wm] overlay=main_w/2-overlay_w/2:main_h/2-overlay_w/2 [out]' '_cache/_small/2005/test'\''s ! ~ weird chars/MVI_0019.mp4'.jpg

*/

    // save the first frame as the video poster image with a play button watermark
    $exec_cmd = "$ffmpeg_cmd -v 0 -y -i $video_file $ffmpeg_filter_options $poster_file";

    $last_error = exec($exec_cmd . " 2>&1", $output, $retvar);
    if ( $retvar != 0 ) {
        error_log( $exec_cmd );
        error_log( $last_error );
        error_log( print_r(array_pop($output), true) );
    }
}

function generate_cached_jpeg_files( $filepath ) { // make versions of JPEG files for the cache
    global $original_photos_dir;
    global $large_image_dimension, $medium_image_dimension, $small_image_dimension, $thumbnail_image_dimension, $jpeg_image_quality;
    global $watermark_font_file, $watermark_font_size, $copyright_owner_name;

    $cached_thumbfile  = get_cache_path( $filepath, '_thumbnails' );
    $cached_smallfile = get_cache_path( $filepath, '_small' );
    $cached_mediumfile = get_cache_path( $filepath, '_medium' );
    $cached_largefile  = get_cache_path( $filepath, '_large' );

    $filepath = "$original_photos_dir/$filepath";

    // find out how the image needs to be rotated
    $exif = exif_read_data($filepath);
    $rotation = 0;
    if (!empty($exif['Orientation'])) {
        switch($exif['Orientation']) {
            case 1: $rotation = 0; break;
            case 8: $rotation = 90; break;
            case 3: $rotation = 180; break;
            case 6: $rotation = 270; break;
            default: $rotation = 0; break;
        }
    }

    // read the image into memory
    $im = ImageCreateFromJpeg($filepath);

    $size = getimagesize($filepath);
    $im = makeScaled($im, $size, $large_image_dimension, $rotation); // create the cached large file and rotate it
    ImageJpeg($im, $cached_largefile, 90); // save the large image file

    $size = array(imagesx($im), imagesy($im));
    $im = makeScaled($im, $size, $medium_image_dimension); // reduce and create the cached medium file
    if ( watermark_feature_enabled() ) { // only watermarking the medium size images
        $year = date( 'Y', timestamp_from_exif_DateTimeOriginal($filepath) );
        $string = "© copyright $copyright_owner_name $year";
        $font_color = imagecolorallocate($im, 0, 0, 0);  // color for watermark font
        $font_background_color = imagecolorallocate($im, 255, 255, 255);
        $x = 10;
        $y = imagesy($im) - 20;
        //imagestring($im, 5, $x, $y, $string, $color); // draw horizontally
        //imagestringup($im, 5, $px, $py, $string, $color); // draw vertically
        imagettftext($im, $watermark_font_size, 0, $x, $y+1, $font_background_color, $watermark_font_file, $string );
        imagettftext($im, $watermark_font_size, 0, $x, $y-1, $font_background_color, $watermark_font_file, $string );
        imagettftext($im, $watermark_font_size, 0, $x+1, $y, $font_background_color, $watermark_font_file, $string );
        imagettftext($im, $watermark_font_size, 0, $x-1, $y, $font_background_color, $watermark_font_file, $string );
        imagettftext($im, $watermark_font_size, 0, $x, $y, $font_color, $watermark_font_file, $string );
    }
    ImageJpeg($im, $cached_mediumfile, $jpeg_image_quality); // save the medium image file

    $size = array(imagesx($im), imagesy($im));
    $im = makeScaled($im,  $size, $small_image_dimension); // now reduce it again to the cached small file
    ImageJpeg($im, $cached_smallfile, $jpeg_image_quality); // save the small image file

    $size = array(imagesx($im), imagesy($im));
    $im = makeScaled($im,  $size, $thumbnail_image_dimension); // now reduce it again to the cached thumbnail file
    ImageJpeg($im, $cached_thumbfile, $jpeg_image_quality); // save the thumbnail image file

    ImageDestroy($im); //clean up the old image
}

function generate_cached_files( $filepath, $overwrite_existing_files=FALSE ) { // make the cached version of the file
    // return 0 if no files were generated, or 1 if one was generated
    global $webserver_user;

    $file = basename($filepath);
    $dir = dirname($filepath);
    $cached_thumbfile = get_cache_path( $filepath, '_thumbnails' );
    $cached_smallfile = get_cache_path( $filepath, '_small' );
    $cached_mediumfile = get_cache_path( $filepath, '_medium' );
    $cached_largefile = get_cache_path( $filepath, '_large' );
    $cache_thumb_dir = dirname($cached_thumbfile);
    $cache_small_dir = dirname($cached_smallfile);
    $cache_medium_dir = dirname($cached_mediumfile);
    $cache_large_dir = dirname($cached_largefile);

    if (!is_file($cached_thumbfile)  ||
        !is_file($cached_smallfile) ||
        !is_file($cached_mediumfile) ||
        !is_file($cached_largefile)  ||
        (handled_filetype($file) == 'video' && (!is_file("$cached_smallfile.jpg") || !is_file("$cached_mediumfile.jpg") || !is_file("$cached_largefile.jpg"))) ||
        $overwrite_existing_files) {

        mkdir_r($cache_thumb_dir);
        mkdir_r($cache_small_dir);
        mkdir_r($cache_medium_dir);
        mkdir_r($cache_large_dir);

        switch ( handled_filetype($file) ) {
            case 'photo':
                generate_cached_jpeg_files( $filepath );
                break;
            case 'video':
                generate_cached_video_files( $filepath, $overwrite_existing_files );
                break;
            case 'audio':
                generate_cached_audio_files( $filepath );
                break;
        }
        if ( @chown($cached_thumbfile, $webserver_user) &&
             @chown($cached_smallfile, $webserver_user) &&
             @chown($cached_mediumfile, $webserver_user) &&
             @chown($cached_largefile, $webserver_user) ) {
            chgrp( $cached_smallfile, $webserver_user );
            chmod( $cached_smallfile, 0664);
            chgrp( $cached_mediumfile, $webserver_user );
            chmod( $cached_mediumfile, 0664);
            chgrp( $cached_largefile, $webserver_user );
            chmod( $cached_largefile, 0664);
            chgrp( $cached_thumbfile, $webserver_user );
            chmod( $cached_thumbfile, 0664);
        }
        elseif (handled_filetype($file) != 'audio') {
            //error_log( "$file -- $cached_mediumfile -- $cached_thumbfile" );
            @chmod( $cached_largefile, 0666);
            @chmod( $cached_mediumfile, 0666);
            @chmod( $cached_smallfile, 0666);
            @chmod( $cached_thumbfile, 0666);
        }
        return 1;
    }
    return 0;
}

function create_static_index_page($overwrite_existing_files) {
    global $html_page_top, $html_page_bottom;

    $html_page_middle = <<<"EOD"
        <script>document.write('Loading... <img src="images/ajax-loader.gif"/>');</script>
        <noscript>
            <p>Sorry, you need to turn on javascript for this site to work properly.</p>
            <p>Without javascript you can only use the <a href="bpg-srv.php?dir=/">basic version</a>.</p>
        </noscript>
EOD;

    if (!is_file('index.html') || $overwrite_existing_files) {
        print "Creating index.html file with values from config.php\n";
        $fp = fopen('index.html', "wb");
        fwrite($fp, $html_page_top);
        fwrite($fp, $html_page_middle);
        fwrite($fp, $html_page_bottom);
        fclose($fp);
        chmod('index.html', 0644);
    }
}

function generate_all_cache_files( $top_path, $filetype, $overwrite_existing_files, $verbose ) {
    // for generating cache files from a command line
    global $cache_dir, $original_photos_dir;

    create_static_index_page($overwrite_existing_files);
    mkdir_r($cache_dir);

    if ($verbose) print "TOP_PATH: $top_path ($filetype)\n";

    if ( $top_path == 'all' ) {
        $top_path = '';
    }
    $files_and_dirs = get_files_and_dirs( "$original_photos_dir/$top_path");
    $files = $files_and_dirs['files'];
    $dirs = $files_and_dirs['dirs'];

    $filepaths = array();

    $number_of_files_generated = 0;
    foreach( $files as $filename ) {
        if ( $top_path == '' ) {
            $filepath = $filename;
        }
        else {
            $filepath = "$top_path/$filename";
        }
        $filepath = ltrim($filepath, '/');
        if ($verbose) print "FILE: $filepath";
        if ( handled_filetype($filepath) == "$filetype" || $filetype == 'all' ) {
            if ($verbose) { print "  GEN\n"; }
            $number_of_files_generated += generate_cached_files( $filepath, $overwrite_existing_files );
        }
        else {
            if ($verbose) { print "  skipped\n"; }
        }
    }
    foreach( $dirs as $dir ) {
        if ( $top_path == '' ) {
            $path = $dir;
        }
        else {
            $path = "$top_path/$dir";
        }
        if ($verbose) print "DIR: $path\n";
        $number_of_files_generated += 1;
        generate_all_cache_files($path, $filetype, $overwrite_existing_files, $verbose);
    }

    // the thumbnails.html files is used for static mode
    $thumbnails_html_file = get_cache_path( ltrim("$top_path/thumbnails.html", '/'), '_thumbnails' );
    $thumbnails_html = render_thumbnail_area_html( get_list_of_files_to_show($top_path), $top_path );
    if ($number_of_files_generated > 0 || !is_file($thumbnails_html_file)) {
        if ($verbose) print "building file $thumbnails_html_file\n";
        $fp = fopen($thumbnails_html_file, "wb");
        fwrite($fp, $thumbnails_html);
        fclose($fp);
        chmod($thumbnails_html_file, 0666);
    }
}

function ajaxImageRotate( $filepath, $direction ) {
    global $original_photos_dir;
    if ( !isAdminUser() ) {
        ajaxError("error: not admin user");
    }
    if ( !exif_editing_feature_enabled() ) {
        ajaxError("error: not exif_editing_feature_enabled");
    }

    // find out how the image is currently rotated by the exif field
    $exif = exif_read_data("$original_photos_dir/$filepath");
    $rotation = 0;
    if (!empty($exif['Orientation'])) {
        switch($exif['Orientation']) {
            case 1: $rotation = 0; break;
            case 8: $rotation = 90; break;
            case 3: $rotation = 180; break;
            case 6: $rotation = 270; break;
            default: $rotation = 0; break;
        }
    }

    // rotate in the requested direction
    switch ( $direction ) {
        case 'cw':  $new_rotation = $rotation - 90; break;
        case 'ccw':  $new_rotation = $rotation + 90; break;
        default:
            ajaxError("error: unknown rotation direction requested");
            break;
    }

    // convert the new rotation to an Exif rotation value
    switch($new_rotation) {
        case 0: $new_orientation = 1; break;
        case 90: $new_orientation = 8; break;
        case 180: $new_orientation = 3; break;
        case 270: $new_orientation = 6; break;
        case -90: $new_orientation = 6; break;
        case 360: $new_orientation = 1; break;
        default:
            ajaxError("error: unknown orientation calculated");
            break;
    }

    // write the Exif Orientation tag in the original file
    writeExifTag( $filepath, 'Orientation', $new_orientation);

    // regenerate the cached files
    generate_cached_files( $filepath, TRUE );

    return 'success';
}

function get_video_thm_path( $filepath ) { // Exif comments for videos are saved in the THM jpeg file
    if ( substr($filepath, -3) == 'avi' || substr($filepath, -3) == 'mov') {
        $filepath = substr_replace($filepath, '.thm', -4);
    }
    elseif ( substr($filepath, -3) == 'AVI' || substr($filepath, -3) == 'MOV' ) {
        $filepath = substr_replace($filepath, '.THM', -4);
    }
    else {
        return FALSE;
    }
    if (is_file($filepath)) {
        return $filepath;
    }
    return FALSE;
}

function read_exif_name_and_date( $filepath ) { // get the exif file name and photo date from the jpeg file
    global $original_photos_dir;
    $filepath = "$original_photos_dir/$filepath";

    $result = '';
    if ( handled_filetype($filepath) == 'photo') {
        $exif = exif_read_data( $filepath );
    }
    elseif ( handled_filetype($filepath) == 'video' ) {
        if (get_video_thm_path($filepath) === FALSE) {
            return $result;
        }
        $exif = exif_read_data( get_video_thm_path($filepath) );
    }
    if (isset($exif['DateTimeOriginal'])) {
        $dt_ary = preg_split( '/[\/: ]/', $exif['DateTimeOriginal']);
        $yyyy = $dt_ary[0];
        $mm = $dt_ary[1];
        $dd = $dt_ary[2];
        $h = $dt_ary[3];
        $m = $dt_ary[4];
        $s =  $dt_ary[5];
        $result = $exif['FileName'] ." ($yyyy-$mm-$dd $h:$m)";
    }
    return $result;
}

function timestamp_from_exif_DateTimeOriginal( $filepath, $format='unix' ) { // get a UNIX timestamp formated time from EXIF data
    $result = '';
    if ( handled_filetype($filepath) == 'photo') {
        $exif = exif_read_data( $filepath );
        list ($yyyy, $mm, $dd, $h, $m, $s, $junk) = preg_split( '/[\/: ]/', $exif['DateTimeOriginal'] . ":junk");
        if ( $format == 'unix' ) {
            $result = mktime( $h, $m, $s, $mm, $dd, $yyyy);
        }
        elseif ( $format == 'yymmdd') {
            $result = "$yyyy$mm$dd$h$m.$s";
        }
    }
    return $result;
}

function writeExifTag( $filepath, $tag, $value, $clear_old_jpeg_com = FALSE ) { // save some EXIF data in the original file
    global $original_photos_dir, $exiftool_cmd;
    $value = trim($value);

    if ( !isAdminUser() ) {
        ajaxError("error: not admin user");
    }
    if ( !exif_editing_feature_enabled() ) {
        ajaxError("error: not exif_editing_feature_enabled");
    }

    // determine the path of the file that will be modified
    $filepath = "$original_photos_dir/$filepath";
    // save comments for movies in the thumbnail jpeg file.
    if ( handled_filetype($filepath) == 'video' ) {
        if ( is_file( substr_replace($filepath, '.thm', -4) ) ) {
            $filepath = substr_replace($filepath, '.thm', -4);
        }
        elseif ( is_file( substr_replace($filepath, '.THM', -4) ) ) {
            $filepath = substr_replace($filepath, '.THM', -4);
        }
    }
    if ( !file_exists($filepath) ) {
        ajaxError("error: file does not exist");
    }
    elseif ( !is_writable($filepath) ) {
        ajaxError("error: file is not writable (try: chmod a+w <file>)");
    }

    list($width, $height, $type, $attr) = getimagesize($filepath);
    if ( image_type_to_mime_type( $type ) == "image/jpeg") { // make sure it's really a jpeg
        switch ( $tag ) {
            case 'Orientation':
                $command = "$exiftool_cmd -overwrite_original -P -n -EXIF:Orientation=".escapeshellarg($value)." ".escapeshellarg($filepath);
                break;
            case 'Comment':
                $command = "$exiftool_cmd -overwrite_original -P -n -EXIF:ImageDescription=".escapeshellarg($value)." -IPTC:Caption-Abstract=".escapeshellarg($value)." -XMP-dc:Description=".escapeshellarg($value)." ".escapeshellarg($filepath);
                break;
            default:
                ajaxError("error: unsupported Exif tag");
                break;
        }

        $output = exec($command, $full_output, $return_var);
        if ( $return_var != 0 ) {
            ajaxError( $output );
        }
        else { // successful completion, keep the file mtime the same for sorting
            chmod($filepath, 0666);
            touch( $filepath, intval(timestamp_from_exif_DateTimeOriginal($filepath)) );
            return 'success';
        }
    }
    else {
        ajaxError("error: the file does not look like a jpeg image");
    }
}

function read_jpeg_comment( $filepath ) { // get the JPEG comment from a file
    global $original_photos_dir, $exiftool_cmd;
    $filepath = "$original_photos_dir/$filepath";

    $comments = '';

    // comments for movies are saved in the thumbnail jpeg file.
    if ( handled_filetype($filepath) == 'video' ) {
        if (is_file( substr_replace($filepath, '.thm', -4))) {
            $filepath = substr_replace($filepath, '.thm', -4);
        }
        elseif (is_file( substr_replace($filepath, '.THM', -4))) {
            $filepath = substr_replace($filepath, '.THM', -4);
        }
    }

    if ( handled_filetype($filepath) == 'photo' || strtolower(substr($filepath, -4, 4)) == ".thm") {

        // EXIF
        $exif = @exif_read_data( $filepath );

        if (isset($exif['ImageDescription']) ) {
            $exif_description = $exif['ImageDescription'];
        }

        // IPTC
        $size = getimagesize($filepath, $info);
        if (isset($info['APP13'])) {
            $iptc = iptcparse($info['APP13']);
            if (isset($iptc['2#120'])) {
                $iptc_description = $iptc['2#120'][0];
            }
        }

        //// XMP
        //// THIS IS TOO SLOW!
        //// derivation of code by Pekka Saarinen http://photography-on-the.net
        //ob_start();
        //readfile($filepath);
        //$source = ob_get_contents();
        //ob_end_clean();
        //$xmpdata_start = strpos($source,"<x:xmpmeta");
        //$xmpdata_end = strpos($source,"</x:xmpmeta>");
        //$xmplenght = $xmpdata_end-$xmpdata_start;
        //$xmpdata = substr($source,$xmpdata_start,$xmplenght+12);
        //$regexp = "/<dc:description>\s*<rdf:Alt>\s*<rdf:li.*>(.+)<\/rdf:li>\s*<\/rdf:Alt>\s*<\/dc:description>/";
        //preg_match ($regexp, $xmpdata, $r);
        //$xmp_description = @$r[1];

        if ( isset($exif_description) && $exif_description != '' ) {
            $comments .= "$exif_description\n";
        }
        if ( isset($iptc_description) && $iptc_description != '' && $iptc_description != $exif_description ) {
            $comments .= "$iptc_description\n";
        }
        if ( isset($xmp_description) && $xmp_description != '' && $xmp_description != $exif_description && $xmp_description != $iptc_description ) {
            $comments .= "$xmp_description\n";
        }
    }
    elseif ( handled_filetype($filepath) == 'audio' ) {
        $tags = id3_get_tag( $filepath );
        $description = $tags['comments'];
        $artist = $tags['artist'];
        $comments .= "$artist. $description\n";
    }
    return trim($comments);
}

//function convert_old_captions($top_path) {
//    //converting from old jpeg comment to metadataworkinggroup.org recommended locations
//    global $original_photos_dir;
//    global $exiftool_cmd;
//
//    print "TOP_PATH: $original_photos_dir/$top_path\n";
//
//    $files_and_dirs = get_files_and_dirs( "$original_photos_dir/$top_path");
//    $files = $files_and_dirs['files'];
//    $dirs = $files_and_dirs['dirs'];
//
//    $filepaths = array();
//
//    foreach( $files as $filename ) {
//        if ( $top_path ) {
//            $filepath = "$top_path/$filename";
//        }
//        else {
//            $filepath = "$filename";
//        }
//        print "FILE: $filepath";
//        if (  handled_filetype($filepath) == 'audio' ) {
//            print "  not a jpeg file. skipped\n";
//            continue;
//        }
//        $old_comment = read_jpeg_comment($filepath);
//        if ( isset($old_comment) && $old_comment != '' ) {
//
//            if ( strpos($old_comment, 'File written by Adobe Photoshop') !== FALSE ||
//                 strpos($old_comment, 'OLYMPUS DIGITAL CAMERA') !== FALSE ||
//                 strpos($old_comment, 'MINOLTA DIGITAL CAMERA') !== FALSE ||
//                 strpos($old_comment, 'GC-QX5 Image') !== FALSE
//                 ) {
//                    print " removing '$old_comment'";
//                    $old_comment = '';
//            }
//            print "  converting '$old_comment'";
//            writeExifTag( $filepath, 'Comment', $old_comment, TRUE );
//        }
//        print "\n";
//    }
//    foreach( $dirs as $dir ) {
//        $path = "$top_path/$dir";
//        print "\nDIR: $path\n";
//        convert_old_captions($path);
//    }
//}

function compare_and_delete_abandoned_cache_files( $path, $original_files, $cache_area ) {
    $cache_area_path = dirname(get_cache_path( "$path/.", $cache_area ));
    $cache_files_and_dirs = get_files_and_dirs( $cache_area_path, FALSE );
    $files_to_delete = $cache_files_and_dirs['files'];
    $files_to_delete = @array_diff( $cache_files_and_dirs['files'], $original_files);  // this makes it up to 30% faster
    if (is_array($files_to_delete)) {
        foreach ($files_to_delete as $i => $old_file) { // look at each file we are about to delete in the _cache dir
            if ($old_file == 'thumbnails.html' || $old_file == 'index.html') {
                unset( $files_to_delete[$i] );
            }
            else {
                // don't delete it if there is a matching filename in the originals directory
                foreach ($original_files as $cur_file) {
                    // if the names match without extensions take it off the list of files to delete
                    if ( substr($old_file, 0, -4) == substr($cur_file, 0, -4) || substr($old_file, 0, -8) == substr($cur_file, 0, -4)) {
                        unset( $files_to_delete[$i] );
                    }
                }
            }
        }
        foreach ( $files_to_delete as $file_to_delete ) {
            unlink("$cache_area_path/$file_to_delete");
        }
    }
}

function purge_abandoned_cache_files( $path ) { // delete cache files that are no longer in the originals
    global $original_photos_dir;
    $files_and_dirs = get_files_and_dirs( "$original_photos_dir/$path" );
    $original_files = $files_and_dirs['files'];

    // delete the large sized files
    compare_and_delete_abandoned_cache_files( $path, $original_files, '_large' );

    // delete the medium sized files
    compare_and_delete_abandoned_cache_files( $path, $original_files, '_medium' );

    // delete the small sized files
    compare_and_delete_abandoned_cache_files( $path, $original_files, '_small' );

    // delete the thumbnail sized files
    compare_and_delete_abandoned_cache_files( $path, $original_files, '_thumbnails' );


/*
    // delete missing directories
//print "<!-- \n";
    $original_dirs = $files_and_dirs['dirs'];
    $cache_path = dirname(dirname(get_cache_path("$path/.", '_thumbnails')));
    $cache_files_and_dirs = get_files_and_dirs( $cache_path, FALSE );
    $dirs_to_delete = @array_diff( $cache_files_and_dirs['dirs'], $original_dirs);  // this makes it up to 30% faster
//print "$path\n";
//print "$cache_path\n";
//print_r($original_dirs);
//print_r($cache_files_and_dirs['dirs']);
    if (is_array($dirs_to_delete)) {
        foreach ($dirs_to_delete as $i => $old_dir) { // look at each dir we are about to delete
            foreach ($original_dirs as $cur_dir) { // compare it to each dir that is still in the original path
                if ( $old_dir == $cur_dir ) { // if the names match
                    unset( $dirs_to_delete[$i] ); // take it off the list of dirs to delete
                } elseif ( $old_dir == '_orders') {
                    unset( $dirs_to_delete[$i] ); // take it off the list of dirs to delete
                } elseif ( $old_dir == '_thumbnails') {
                    unset( $dirs_to_delete[$i] ); // take it off the list of dirs to delete
                } elseif ( $old_dir == '_medium') {
                    unset( $dirs_to_delete[$i] ); // take it off the list of dirs to delete
                }
            }
        }
        foreach ( $dirs_to_delete as $dir_to_delete ) {
//          print "removing dir $cache_path/$dir_to_delete/";
            rmdir_r("$cache_path/$dir_to_delete");
        }
    }
//print_r($dirs_to_delete);
//print "-->";
*/
}
function rmdir_r($path) { // delete a directory recursively
    if (!is_dir($path)) return false;
    $stack = Array($path);
    while ($dir = array_pop($stack)) {
        if (@rmdir($dir)) continue;
        $stack[] = $dir;
        $dh = opendir($dir);
        while (($child = readdir($dh)) !== false) {
            if ($child[0] == '.') continue;
            $child = $dir . '/' . $child;
            if (is_dir($child)) $stack[] = $child;
            else unlink($child);
        }
    }
    return true;
}
function deleteOriginalFile( $filepath ) { // delete an original file
    global $original_photos_dir;
    $path = dirname($filepath);
    $filepath = "$original_photos_dir/$filepath";

    if ( !isAdminUser() ) {
        ajaxError("error: not admin user");
    }

    if (unlink($filepath)) {
        if (handled_filetype( $filepath ) == 'video') {
            if (substr($filepath, -3) == 'avi' || substr($filepath, -3) == 'mov') {
                $filepath = substr_replace($filepath, '.thm', -4);
            }
            elseif (substr($filepath, -3) == 'AVI' || substr($filepath, -3) == 'MOV') {
                $filepath = substr_replace($filepath, '.THM', -4);
            }
            unlink( $filepath );// convert to .thm file to delete it also
        }
        purge_abandoned_cache_files( "$path/" );

        return 'success';
    }

    ajaxError("error: unable to delete file.");
}

function getSiteStats() {
    global $original_photos_dir, $cache_dir;
    global $echo_cmd, $find_cmd, $grep_cmd, $wc_cmd, $du_cmd, $awk_cmd;

    if ( ! site_stats_feature_enabled() ) { return ''; }

    $stats_cache_file = "$cache_dir/stats";
    $last_mod_dir = "$cache_dir/_small/" . @exec("ls -tr $cache_dir/_small/|tail -1");
    //if ( !is_file($stats_cache_file) || (time() - filemtime($stats_cache_file) > 604800) ) {
    if ( !is_file($stats_cache_file) || (filemtime($stats_cache_file) < filemtime($last_mod_dir)) ) {
        touch($stats_cache_file);
        @system("$find_cmd $original_photos_dir -type f| $grep_cmd -iv 'thm'| $wc_cmd -l > $stats_cache_file");
        @system("$echo_cmd ' files, ' >> $stats_cache_file");
        @system("$du_cmd -sh $original_photos_dir |$awk_cmd '{print $1}' >> $stats_cache_file");
        @system("$echo_cmd '' >> $stats_cache_file");
    }
    //for testing set the mtime back a week
    //touch($stats_cache_file, filemtime($stats_cache_file) - 604800);

    return str_replace("\n", '', @file_get_contents($stats_cache_file));
}

function cartAddItem( $filepath ) {
    $_SESSION['cart'][$filepath] = 1;
    return 'success';
}
function cartRemoveItem( $filepath ) {
    unset( $_SESSION['cart'][$filepath] );
    return 'success';
}
function cartRemoveAll() {
    unset( $_SESSION['cart'] );
    return 'success';
}
function cartAddAll( $path ) {
    global $original_photos_dir;
    $local_path = "$original_photos_dir/$path";
    $files_and_dirs = get_files_and_dirs( $local_path );
    $files = $files_and_dirs['files'];
    sort($files);
    foreach( $files as $filename ) {
        cartAddItem( "$path/$filename" );
    }
    return 'success';
}
function getCart() {
    $cart = array();
    if (isset($_SESSION['cart'])) {
        $cart['file'] = array_keys($_SESSION['cart']);
        $cart['quantity'] = array_values($_SESSION['cart']);
    }
    else {
        $cart['file'] = array();
        $cart['quantity'] = array();
    }
    return $cart;
}

function calculateDownloadTime(&$bytes){ // estimate of download time for a zip file
    global $upstream_bandwidth_bits;
    $b = (int)$bytes;
    $min = $b * 8 / 60 / $upstream_bandwidth_bits;
    if ($min < 1) {
        return "1 min";
    }
    return number_format( $min, 0) .' min';

    $s = array('B', 'kB', 'MB', 'GB', 'TB');
    if ($b < 0){
        return "0 ".$s[0];
    }
    $con = 1024;
    $e = (int)(log($b,$con));
    return number_format($b/pow($con,$e),0,'.','').' '.$s[$e];
}

function update_noauth_cache_link() {
    // if the useragent says this an android browser then serve the cache files from a different URL
    // that does not need basic auth since the android video players can't handle that
    // to keep this more secure we'll change the symlink daily
}

function render_thumbnail_area_html( $files, $path ) { //original version
    global $original_photos_dir, $cart;
    $html = '';

    if ( $path != 'cart') {
        $local_path = $original_photos_dir;
        if ( !empty($path) ) { $local_path .= "/$path"; }
        $files_and_dirs = get_files_and_dirs( $local_path );

        $html .= "<ol id='folders' class='nav nav-tabs nav-stacked'>\n";
        foreach( $files_and_dirs['dirs'] as $dir ) {
            if ($path) {
                $dir_link = "$path/$dir";
            }
            else {
                $dir_link = "$dir";
            }
            $html .= "  <li><a href=\"?dir=$dir_link\">$dir</a></li>\n";
        }
        $html .= "</ol>\n";
    }

    $html .= "<ol id='media' class='thumbnails'>\n";
    foreach( $files as $file ) {
        $filename = basename($file);
        if ( PHP_SAPI != 'cli' ) {
            generate_cached_files($file);
        }
        $thumbnail_relative_url = get_cache_path( $file, '_thumbnails' );
        $title = read_exif_name_and_date($file);
        $caption = read_jpeg_comment($file);
        list($w, $h) = getimagesize($thumbnail_relative_url); // seems Firefox needs this because the inline-block CSS rendering is buggy.

        $slide_url = urlencode_path(get_cache_path( $file, '_small' ));
        $thumbnail_absolute_url = get_load_balanced_url($thumbnail_relative_url);
        $thumbnail_onerror = '';
        if ( load_balancing_feature_enabled() ) {
            // this is not XHTML compliant, but it is an effective method to fall back to the
            // original host if the faster mirrors are not available
            // I have not yet found a good way to move this to an external javascript file.
            $thumbnail_onerror = " onerror=\"javascript:this.onerror='';this.src='". urlencode_path($thumbnail_relative_url) ."';\"";
        }

//        $html .= "
//    <li id=\"$filename\">
//        <div class='thumbnail'>
//            <a href=\"$slide_url\" class='slide'><img class='thumb_img' src=\"$thumbnail_absolute_url\" title=\"$title\" alt=\"$title\" width='$w' height='$h' $thumbnail_onerror /></a>
//            <p>$caption</p>
//        <div>
//    </li>
//";

            //<p>
            //    <button class='btn btn-primary'>Action</button>
            //    <button class='btn''>Action</button>
            //</p>

        $html .= "
    <li id=\"$filename\">
        <a href=\"$slide_url\" class='slide'><img class='thumb_img' src=\"$thumbnail_absolute_url\" title=\"$title\" alt=\"$title\" width='$w' height='$h' $thumbnail_onerror /></a>
        <p class='thumb_comments'>$caption</p>
    </li>
";

        @ob_flush(); // force browser to display everything available so far
        flush();
    }

        $html .= "
    <li id=\"last_slide\" style=\"display:none;\">
        <a href=\"images/the_end.png\" class='slide'><img class='thumb_img' src=\"images/the_end.png\"/></a>
        <p class='thumb_comments'></p>
    </li>
";

    $html .= "</ol>\n";
    return $html;
}

function get_list_of_files_to_show( $path ) {
    global $original_photos_dir, $cart;

    $file_list = array();

    purge_abandoned_cache_files( $path );
    if (buy_prints_feature_enabled()) {
        delete_old_order_files();
    }

    if ( $path == 'cart' && $cart) {
        foreach ( $cart as $file => $quantity ) {
            $file = stripslashes($file);
            $file_list[] = $file;
        }
        sort( $file_list ); // sort by name
    }
    else {
        $local_path = $original_photos_dir;
        if ( !empty($path) ) { $local_path .= "/$path"; }

        $files_and_dirs = get_files_and_dirs( $local_path );
        foreach( $files_and_dirs['files'] as $filename ) {
            if ( $path ) {
                $file = "$path/$filename";
            }
            else {
                $file = $filename;
            }
            $file_list[] = $file;
        }
    }
    return $file_list;
}


//function getExifInfo( $filepath ) {  // get EXIF info. returns $info['name']=$value;
//
//    if ( handled_filetype($filepath) == 'video' ) {
//        if (substr($filepath, -3) == 'avi' || substr($filepath, -3) == 'mov') {
//            $filepath = substr_replace($filepath, '.thm', -4);
//        }
//        elseif (substr($filepath, -3) == 'AVI' || substr($filepath, -3) == 'MOV') {
//            $filepath = substr_replace($filepath, '.THM', -4);
//        }
//    }
//    $file = get_cache_path( $filepath, 'original' );
//    $exif = @exif_read_data($file, 0, true);
//
//    if ( $exif !== FALSE ) {
//
//        $info['Date/Time'] = $exif['EXIF']['DateTimeOriginal'];
//        $info['Filename'] = $exif['FILE']['FileName'];
//        $info['Size'] = round($exif['FILE']['FileSize']/1024/1000, 1) .' Mb';
//        $info['Width x Height'] = $exif['EXIF']['ExifImageWidth'].' x '.$exif['EXIF']['ExifImageLength'];
//        $info['Camera'] = $exif['IFD0']['Model'];
//        $info['Aperture'] = $exif['COMPUTED']['ApertureFNumber'];
//
//        $v = $exif['EXIF']['ExposureTime'];
//        if (strpos($v, '/')) {
//            list ($dividend, $divisor) = split('/', $v);
//            if ( is_numeric($dividend) && is_numeric($divisor) ) {
//                $cval = $dividend/$divisor;
//            }
//        }
//        if ( substr($dividend, -1, 1)=='0' && substr($divisor, -1, 1)=='0' ) {
//            $dividend = trim($dividend, '0');
//            $divisor = trim($divisor, '0');
//        }
//        $info['Exposure'] = "$dividend/$divisor sec";
//
//        $v = $exif['EXIF']['FocalLength'];
//        if (strpos($v, '/')) {
//            list ($dividend, $divisor) = split('/', $v);
//            if ( is_numeric($dividend) && is_numeric($divisor) ) {
//                $v = $dividend/$divisor;
//            }
//        }
//        if ($exif['EXIF']['FocalLengthIn35mmFilm']) {
//            $v .= '(FocalLengthIn35mmFilm = '.$exif['EXIF']['FocalLengthIn35mmFilm'].')';
//        }
//        $info['Focal Length'] = $v;
//
//        $info['ISO Sensitivity'] = $exif['EXIF']['ISOSpeedRatings'];
//
//        if ($exif['EXIF']['Flash']) $v = 'yes';
//        else $v = 'no';
//        $info['Flash'] = $v;
//
//        //Exposure program that the camera used when image was taken. '1' means manual control, '2' program normal, '3' aperture priority, '4' shutter priority, '5' program creative (slow program), '6' program action(high-speed program), '7' portrait mode, '8' landscape mode.
//        switch ($exif['EXIF']['ExposureProgram']) {
//            case '0': $v = 'Automatic'; break;
//            case '1': $v = 'Manual control (M)'; break;
//            case '2': $v = 'Auto shutter and aperture (P)'; break;
//            case '3': $v = 'Aperture Priority (A)'; break;
//            case '4': $v = 'Shutter Priority (S)'; break;
//            case '5': $v = 'program creative'; break;
//            case '6': $v = 'program action'; break;
//            case '7': $v = 'portrait'; break;
//            case '8': $v = 'landscape'; break;
//            default: $v = $exif['EXIF']['ExposureProgram'];
//        }
//        $info['Shooting Mode'] = $v;
//
//        //Exposure metering method. '0' means unknown, '1' average, '2' center weighted average, '3' spot, '4' multi-spot, '5' multi-segment, '6' partial, '255' other.
//        switch ($exif['EXIF']['MeteringMode']) {
//            case '1': $v = 'Average'; break;
//            case '2': $v = 'Center-weighted'; break;
//            case '3': $v = 'Spot'; break;
//            case '4': $v = 'Multi-spot'; break;
//            case '5': $v = 'Matrix (multi-segment)'; break;
//            case '6': $v = 'Partial'; break;
//            default: $v = $exif['EXIF']['MeteringMode'];
//        }
//        $info['Metering mode'] = $v;
//
//        switch ($exif['IFD0']['Orientation']) {
//            case 1: $v = 'Landscape (horizontal)'; break;
//            case 3: $v = 'Inverted Landscape'; break;
//            case 8: $v = 'Portrait (vertical)'; break;
//            case 6: $v = 'Portrait (vertical)'; break;
//            default: $v = ''; break;
//        }
//        $info['Orientation'] = $v;
//
//        return( $info );
//    }
//    else {
//        return FALSE;
//    }
//}

//function get_exif_embedded_thumbnail( $filepath ) { // display the embedded EXIF thumbnail.  Currently unused.
//    $file = get_cache_path( $filepath, 'original' );
//    $image = exif_thumbnail($file, $width, $height, $type);
//    if ($image!==false) {
//        header('Content-type: ' .image_type_to_mime_type($type));
//        echo $image;
//        exit;
//    }
//    else {
//        echo 'No thumbnail available';
//    }
//}

function do_rss( $path ){ // return an rss version of the browser page
    global $original_photos_dir, $home_site_name, $copyright_owner_name;
    $local_path = "$original_photos_dir/$path";
    $files_and_dirs = get_files_and_dirs( $local_path );
    $files = $files_and_dirs['files'];
    $original_path = "$original_photos_dir/$path";
    $title = basename($path);
    $site_url = dirname($_SERVER['SCRIPT_URI']) . '/';

    header("Content-Type: text/xml");
    header("Pragma: no-cache");
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $xml .= "<rss version=\"2.0\" xmlns:media=\"http://search.yahoo.com/mrss\" xmlns:atom=\"http://www.w3.org/2005/Atom\" xmlns:apple-wallpapers=\"http://www.apple.com/ilife/wallpapers\">\n";
    $xml .= "<channel>\n";
    $xml .= "  <title>$home_site_name photocast $title</title>\n";
    $xml .= "  <link>$site_url</link>\n";
    $xml .= "  <language>en-US</language>\n";
    $xml .= "  <generator>$site_url</generator>\n";
    $xml .= "  <lastBuildDate>". date(r, filemtime( $original_path )) ."</lastBuildDate>\n";
    $xml .= "  <copyright>". date(Y, filemtime( $original_path )) ." $copyright_owner_name</copyright>\n";
    $xml .= "  <ttl>86400</ttl>\n";
    $xml .= "\n";

    foreach( $files as $file ) {
        $large_path = $site_url . urlencode_path( get_cache_path( "$path/$file", '_large' ) );
        $thumb_path = $site_url . urlencode_path( get_cache_path( "$path/$file", '_thumbnails' ) );
        $xml .= "  <item>\n";
        $comments = read_jpeg_comment( "$path/$file" );
        if ( ! $comments ) $comments = read_exif_name_and_date("$path/$file");
        $xml .= "    <title>$comments</title>\n";
        $xml .= "    <link>$site_url?dir=$path</link>\n";
        $xml .= "    <pubDate>". date(r, filemtime("$local_path/$file")) ."</pubDate>\n";
        $xml .= "    <media:thumbnail url=\"$thumb_path\"/>\n";
        $xml .= "    <media:content url=\"$large_path\"/>\n";
        $xml .= "  </item>\n\n";
    }
    $xml .= "</channel>\n";
    $xml .= "</rss>\n";
    print $xml;
} // end of do_rss

function do_rss_announcement() { // return rss summary
    global $original_photos_dir, $home_site_name, $copyright_owner_name;
    $path = get_most_recent_folder();
    $site_url = dirname($_SERVER['SCRIPT_URI']) . '/';

    $local_path = "$original_photos_dir/$path";
    $files_and_dirs = get_files_and_dirs( $local_path );
    $files = $files_and_dirs['files'];
    $original_path = "$original_photos_dir/$path";
    $title = basename($path);
    $pubDate = date('r', filemtime($original_path));
    $pubYear = date('Y', filemtime($original_path));

    header("Content-Type: text/xml");
    header("Pragma: no-cache");
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $xml .= "<rss version=\"2.0\" xmlns:media=\"http://search.yahoo.com/mrss\" xmlns:atom=\"http://www.w3.org/2005/Atom\" xmlns:apple-wallpapers=\"http://www.apple.com/ilife/wallpapers\">\n";
    $xml .= "<channel>\n";
    $xml .= "  <title>$home_site_name photos</title>\n";
    $xml .= "  <description>a photo collection</description>\n";
    $xml .= "  <language>en-US</language>\n";
    $xml .= "  <link>$site_url</link>\n";
    $xml .= "  <generator>$site_url</generator>\n";
    $xml .= "  <lastBuildDate>$pubDate</lastBuildDate>\n";
    $xml .= "  <pubDate>$pubDate</pubDate>\n";
    $xml .= "  <copyright>$pubYear $copyright_owner_name</copyright>\n";
    $xml .= "  <docs>http://www.rssboard.org/rss-specification</docs>\n";
    $xml .= "  <language>en</language>\n";
    $xml .= "  <ttl>86400</ttl>\n";
    $xml .= "\n";

    $html = "      <ul>\n";
    //$c = 0;
    //foreach( $files as $file ) {
    //    $comments = read_jpeg_comment( "$path/$file" );
    //    if ( $comments ) {
    //        $c++;
    //        $html .= "<li>$comments</li>\n";
    //        if ($c > 5) {
    //            break;
    //        }
    //    }
    //}
    $files_by_type = get_files_grouped_by_type($files);
    if (count($files_by_type['photo']) > 0) {
        $html .= "        <li>" . count($files_by_type['photo']) . " new photos</li>\n";
    }
    if (count($files_by_type['video']) > 0) {
        $html .= "        <li>" . count($files_by_type['video']) . " new videos</li>\n";
    }
    $html .= "      </ul>\n";

    $xml .= "  <item>\n";
    $xml .= "    <title>$title</title>\n";
    $xml .= "    <pubDate>$pubDate</pubDate>\n";
    $xml .= "    <link>$site_url?dir=$path</link>\n";
    $xml .= "    <guid>$site_url?dir=$path</guid>\n";
    $xml .= "    <description><![CDATA[ \n$html    ]]></description>\n";
    $xml .= "  </item>\n\n";
    $xml .= "</channel>\n";
    $xml .= "</rss>\n";
    print $xml;
} // end of do_public_rss


function most_recent_photo_folder_rss() { // rss feed for the most recent folder of files
    $mostrecentdir = get_most_recent_folder();
    do_rss( "$mostrecentdir/" );
}

function get_most_recent_folder() {
    global $original_photos_dir;
    $local_path = $original_photos_dir;

    $files_and_dirs = get_files_and_dirs( $local_path );
    $files = $files_and_dirs['files'];
    $dirs = $files_and_dirs['dirs'];

    // get the most recent year directory
    sort($dirs);
    $mostrecentdir = $dirs[count($dirs)-1];

    $yeardir = $mostrecentdir;
    $local_path .= "/$mostrecentdir";
    $files_and_dirs = get_files_and_dirs( $local_path );
    $files = $files_and_dirs['files'];
    $dirs = $files_and_dirs['dirs'];

    $mostrecentdir = $dirs[0];
    foreach ($dirs as $dir) {
        if (filemtime( "$local_path/$dir" ) > filemtime( "$local_path/$mostrecentdir" )) {
            $mostrecentdir = $dir;
        }
    }
    $mostrecentdir = "$yeardir/$mostrecentdir";

    return "$mostrecentdir";
}

function delete_old_order_files(){ // remove order files that are no longer needed
    global $cache_dir;

    // clear old originals in _cache/_orders
    $orderdir = "$cache_dir/_orders/originals";
    $files_and_dirs = get_files_and_dirs( $orderdir );
    $files = $files_and_dirs['files'];
    if (is_array($files)) {
        foreach ($files as $file) {
            // delete files that are older than 15 days
            if ( filemtime( "$orderdir/$file" ) < (time() - (15 * 86400)) ) {
                @unlink ("$orderdir/$file");
            }
        }
    }

    // clear old thumbnails in _cache/_orders/thumbnails
    $orderdir = "$cache_dir/_orders/thumbnails";
    $files_and_dirs = get_files_and_dirs( $orderdir );
    $files = $files_and_dirs['files'];
    if (is_array($files)) {
        foreach ($files as $file) {
            // delete files that are older than 7 days
            if ( filemtime( "$orderdir/$file" ) < (time() - (7 * 86400)) ) {
                @unlink ("$orderdir/$file");
            }
        }
    }

    // clear old order copies in _cache/_orders
    $orderdir = "$cache_dir/_orders";
    $only_handled_filetypes = FALSE;
    $files_and_dirs = get_files_and_dirs( $orderdir, $only_handled_filetypes );
    $files = $files_and_dirs['files'];
    if (is_array($files)) {
        foreach ($files as $file) {
            // delete order files that are older than 120 days
            if ( filemtime( "$orderdir/$file" ) < (time() - (120 * 86400)) && $file != 'index.html' ) {
                @unlink ("$orderdir/$file");
            }
        }
    }

} // end of delete_old_order_files function


function get_shutterfly_buyprints_form() { // submit the cart to Shutterfly for printing
    global $cache_dir, $cart, $original_photos_dir, $print_lab_orders_url;
    $site_url = dirname($_SERVER['SCRIPT_URI']) . '/';

    $cart_count = count($cart);
    $orderdir = "$cache_dir/_orders";

    mkdir_r( $orderdir );  // make sure the _orders directory exists
    mkdir_r( "$orderdir/originals" );  // make sure the _orders/originals directory exists
    mkdir_r( "$orderdir/thumbnails" );  // make sure the _orders/thumbnails directory exists

    if (count($cart) < 1 ) {
        print "sorry, you need to put at least 1 file in your cart before ordering prints";
        return FALSE;
    }

    // post the order to the Shutterfly site
    // redirect the user to Shutterfly to complete the order

    $html = "<form id='ShutterflyOrderForm'action='//www.shutterfly.com/c4p/UpdateCart.jsp' method='post'>
    <div>
        <p>Your files are ready for printing.</p>
        <p>
        Continue to Shutterfly to complete your order.
        <button class='btn' onclick=\"document.getElementById('ShutterflyOrderForm').submit()\">Checkout</button>
        </p>
        <input type='hidden' name='protocol' value='SFP,100'/>
        <input type='hidden' name='pid' value='C4PP'/>
        <input type='hidden' name='psid' value='TEST'/>
        <input type='hidden' name='referid' value='benspicgallery'/>
        <input type='hidden' name='addim' value='1'/>

        <input type='hidden' name='returl' value='$site_url'/>
        <input type='hidden' name='imnum' value='$cart_count'/>
    ";

    // copy all the files to the orders dir
    $i = 0;
    foreach ( $cart as $filepath => $quantity ) {
        $i++;
        $filepath = stripslashes($filepath);
        $filename = basename($filepath);

        if ( handled_filetype($filepath) != 'photo' ) {
            continue; // only print JPEG files
        }

        //copy the originals
        $original = "$original_photos_dir/$filepath";
        $destination = "$orderdir/originals/$filename";
        if (!@copy($original, $destination)) {
            if ( !file_exists($destination) ) {
                $html .= "failed to copy $original to $destination\n";
            }
        }
        $original_url = "$print_lab_orders_url/originals/$filename";// . urlencode_path($filepath);
        $original_file = "$orderdir/originals/$filename";;
        $photo_caption = substr(read_jpeg_comment($filepath), 0, 79);

        //copy the thumbnails
        $original = get_cache_path( $filepath, '_thumbnails' );
        $thumbname = substr_replace($filename, "thm.jpg", -3, 3);
        $destination = "$orderdir/thumbnails/$thumbname";
        if (!@copy($original, $destination)) {
            if (!file_exists($destination)) {
                $html .= "failed to copy $original to $destination\n";
            }
        }

        $thumb_url = "$print_lab_orders_url/thumbnails/$thumbname"; //. urlencode_path(get_cache_path($filepath,'_thumbnails'));
        $thumb_file = "$orderdir/thumbnails/$thumbname";

        list ($orig_width, $orig_height) = getimagesize($original_file);
        list ($thumb_width, $thumb_height) = getimagesize($thumb_file);

        $html .= "
        <input type='hidden' name='imraw-$i' value=\"$original_url\"/>
        <input type='hidden' name='imrawwidth-$i' value=\"$orig_width\"/>
        <input type='hidden' name='imrawheight-$i' value=\"$orig_height\"/>
        <input type='hidden' name='imthumb-$i' value=\"$thumb_url\"/>
        <input type='hidden' name='imthumbwidth-$i' value=\"$thumb_width\"/>
        <input type='hidden' name='imthumbheight-$i' value=\"$thumb_height\"/>
        <input type='hidden' name='imbkprnta-$i' value=\"$photo_caption\"/>
        ";
    }
    $html .= "</div></form>";

    // save a copy of this order locally for future reference
    $saved_order_file = "$cache_dir/_orders/". $_SERVER['REMOTE_ADDR'] ."-". time() .".html";
    file_put_contents( $saved_order_file, $html );

    print $html;
}

function do_download_zipped_cart( $picture_size ) { // download a zip file with of all the files in the cart
    global $cart, $cache_dir, $original_photos_dir, $zip_cmd;

    if ( !zip_download_feature_enabled() ) {
        print "The zip download feature is not enabled.";
        return FALSE;
    }
    if ( count($cart) < 1 ) {
        print "Sorry, you need to put at least 1 file in your cart before requesting a zip download.";
        return FALSE;
    }
    if ( count($cart) > 50 && $picture_size != 'medium' ) {
        // put a cap on the download size since this is very CPU & bandwidth intensive
        print "Sorry, you can only download 50 full sized files at a time.";
        return FALSE;
    }

    $ipaddr = $_SERVER['REMOTE_ADDR'];
    $zipfile = "$cache_dir/$ipaddr.zip";

    // delete any old file before creating a new one for this user
    if (is_file($zipfile)) { unlink($zipfile); }

    $script_dir = dirname( $_SERVER["SCRIPT_FILENAME"] );
    $myfiles = "";
    foreach ( $cart as $filepath => $quantity ) {
        // add file to zip archive
        $filepath = safe_path( $filepath );
        if ( $picture_size == 'medium' ) {
            $filename = "$script_dir/" . get_cache_path( $filepath, '_large' );
        }
        else { // original
            $filename = "$original_photos_dir/$filepath";
        }
        $myfiles .= "\"$filename\" ";
    }
    // 10 seconds for 45 files 154 MB
    // 22 seconds for 101 files 358 MB
    // 80 seconds for 280 files 1158 MB
    exec("$zip_cmd -j -0 \"$zipfile\" $myfiles");

/*  // 120 seconds for 45 files 154 MB
    foreach ( $cart as $filepath => $quantity ) {
        // add file to zip archive
        $filepath = safe_path( $filepath );
        $filename = "$local_path/$filepath";
        $command = "$zip_cmd -j -0 \"$zipfile\" \"$filename\"";
        $return = exec($command);
    }

  // 80 seconds for 45 files 154MB
    $zip = new ZipArchive();
    if ($zip->open($zipfile, ZIPARCHIVE::CREATE)!==TRUE) {
        exit("cannot open <$zipfile>\n");
    }
    foreach ( $cart as $filepath => $quantity ) {
        // add file to zip archive
        $filepath = safe_path( $filepath );
        $filename = "$local_path$filepath";
        $zip->addFile($filename);
    }
    $zip->close();
*/

    //return zip archive to user
    if ($fd = fopen ($zipfile, "r")) {
        $size= filesize($zipfile);
        header("Content-Description: File Transfer");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=\"photos.zip\"");
        header("Content-Length: ".$size);
        header("Cache-control: private"); //use this to open files directly
        //@readfile($zipfile);
        while(!feof($fd)) {
            $buffer = fread($fd, 2048);
            echo $buffer;
        }
        fclose ($fd);
        // delete the file after it's been transfered
        unlink($zipfile);
    }
} // end of do_download_zipped_cart function



function exif_editing_feature_enabled(){
    global $enable_exif_editing_feature;
    return $enable_exif_editing_feature;
}
function zip_download_feature_enabled(){
    global $enable_zip_download_feature;
    return $enable_zip_download_feature;
}
function ffmpeg_video_feature_enabled(){
    global $enable_ffmpeg_video_feature;
    return $enable_ffmpeg_video_feature;
}
function site_stats_feature_enabled(){
    global $enable_site_stats_feature;
    return $enable_site_stats_feature;
}
function load_balancing_feature_enabled(){
    global $enable_load_balancing_feature;
    return $enable_load_balancing_feature;
}
function buy_prints_feature_enabled(){
    global $enable_buy_prints_feature;
    return $enable_buy_prints_feature;
}
function watermark_feature_enabled(){
    global $enable_watermark_feature;
    return $enable_watermark_feature;
}


function test_ffmpeg_cmd(){
    global $ffmpeg_cmd;
    if (is_executable($ffmpeg_cmd)) return TRUE;
    else return FALSE;
}
function test_exiftool_cmd(){
    global $exiftool_cmd;
    if (is_executable($exiftool_cmd)) return TRUE;
    else return FALSE;
}
function test_zip_cmd(){
    global $zip_cmd;
    if (is_executable($zip_cmd)) return TRUE;
    else return FALSE;
}
function test_exif_features(){
    if (function_exists('exif_read_data')) return TRUE;
    else return FALSE;
}
function test_gd2_features(){
    if (function_exists('imageCreateFromString') &&
        function_exists('imageCreateTrueColor')  &&
        function_exists('imageCreateFromJpeg'))
    return TRUE;
    else return FALSE;
}
function test_readable_photo_dir(){
    global $original_photos_dir;
    if (is_dir($original_photos_dir)) return TRUE;
    else return FALSE;
}
function test_writable_cache_dir(){
    global $cache_dir;
    mkdir_r($cache_dir);
    if (is_dir($cache_dir) && is_writable($cache_dir)) return TRUE;
    else return FALSE;
}

function test_dependancies() {
    // test for the dependancies and display the diagnostics page if there are any failures
    // if it's enabled, test for it and don't proceed until it's manually disabled
    // if it's disabled, ignore it.
    if (!test_gd2_features()    ||
        !test_exif_features()   ||
        !test_readable_photo_dir()  ||
        !test_writable_cache_dir()  ||
        (ffmpeg_video_feature_enabled() && !test_ffmpeg_cmd()) ||
        (exif_editing_feature_enabled() && !test_exiftool_cmd()) ||
        (zip_download_feature_enabled() && !test_zip_cmd())
      ){
        return FALSE;
    }
    return TRUE;
}
function do_initial_setup_help() {
    global $gallery_name, $original_photos_dir, $cache_dir, $html_page_top, $html_page_bottom;
    global $ffmpeg_cmd, $exiftool_cmd, $zip_cmd;

    $all_the_features_are_working = test_dependancies();
    $the_initial_cache_has_been_generated = file_exists('index.html');

    print $html_page_top;

    print "<h2>BGP Setup</h2>\n";

    if ($all_the_features_are_working) {
        if ($the_initial_cache_has_been_generated) {
            print "<p class='lead'>It looks like your gallery is all setup.  View <a href='./'>your gallery here</a>.</p>\n";
        }
        else {
            print "<p class='lead'>It looks like your gallery is almost ready.  You just need to run <code>php bpg-srv.php -g -d \"all\" -t all</code> to generate the cached files.</p>\n";
        }
    }
    else { // if something wasn't working then

        print "<p class='lead'>Thank you for installing this program. It looks like you need to configure a few things to make this gallery work.</p>
        <p>Please edit the variables in the file <code>config.php</code> to resolve each of the issues listed below.</p>
        ";

        if ( !test_readable_photo_dir() ) {
            print "
            <div class='alert alert-block alert-error'>
                <p>You must set the location for your original photos:<p>
                <p><code>$original_photos_dir/</code> does not exist</p>
            </div>";
        }
        if ( !test_writable_cache_dir() ) {
            print "
            <div class='alert alert-block alert-error'>
                <p>The \$cache_dir must be writable:</p>
                <p><code>$cache_dir/</code> must be writable by the web server</p>
            </div>";
        }
        if ( !test_gd2_features() ) {
            print "
            <div class='alert alert-block alert-error'>
                <p>PHP is missing the GD2 features:</p>
                <p>Please install the GD module for PHP.</p>
            </div>";
        }
        if ( !test_exif_features() ) {
            print "<div class='alert alert-block alert-error'>
                <p>PHP is missing the EXIF features:<p>
                <p>Please install a version of PHP that supports EXIF.</p>
            </div>";
        }
        if ( !test_ffmpeg_cmd() && ffmpeg_video_feature_enabled() ) {
            print "<div class='alert alert-block alert-error'>
                <p>ffmpeg is needed for converting movies to the h264 mp4 format:</p><p>";
                if (file_exists($ffmpeg_cmd)) print "ffmpeg is not executable by the webserver (try <code>chmod 777 $ffmpeg_cmd</code> to fix this)";
                else print "<code>$ffmpeg_cmd</code> does not exist.  Please install it or disable this feature by setting \$enable_ffmpeg_video_feature to FALSE.";
            print "</p></div>";
        }

        if ( !test_exiftool_cmd() && exif_editing_feature_enabled() ) {
            print "<div class='alert alert-block alert-error'>
                <p>exiftool is needed for editing comments and rotating jpeg files:</p><p>";
            if (file_exists($exiftool_cmd)) print "exiftool is not executable by the webserver (try <code>chmod 777 $exiftool_cmd</code> to fix this)";
            else print "<code>$exiftool_cmd</code> is missing. Please install it or disable this feature by setting \$enable_exif_editing_feature to FALSE\n";
            print "</p></div>";
        }

        if ( !test_zip_cmd() && zip_download_feature_enabled() ) {
            print "<div class='alert alert-block alert-error'>
                <p>zip is needed for downloading photos:</p><p>";
            if (file_exists($zip_cmd)) print "zip is not executable by the webserver (try <code>chmod 777 $zip_cmd</code> to fix this)";
            else print "<code>$zip_cmd</code> is missing. Please install it or disable this feature by setting \$enable_zip_download_feature to FALSE";
            print "</p></div>";
        }
    }

$html_page_bottom = <<<"EOD"

      </div>

      <div id="push"></div>
    </div>

    <div id="footer">
      <div class="container">
        <p id='muted credit'>Powered by <a href='https://github.com/benroy73/bpg'>BPG</a> - <span id='site_stats'></span></p>
      </div>
    </div>

    <!-- Placed at the end of the document so the pages load faster -->
    <script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js'></script>
    <script type='text/javascript' src="bootstrap/js/bootstrap.min.js"></script>

    <script type='text/javascript'>
      $google_analytics_js
      $awstats_js
    </script>

  </body>
</html>
EOD;

    print $html_page_bottom;
}


//------------------------------------------------------------
// CLI command
//------------------------------------------------------------
if ( PHP_SAPI == 'cli' ) { // this is a command line execution
    $opts = getopt("vchgd:t:o");
    $generate_cache = FALSE;
    $filetype = FALSE;
    $overwrite_existing_files = FALSE;
    $print_help_message = FALSE;
    $verbose = FALSE;
    foreach (array_keys($opts) as $opt) switch ($opt) {
        case 'g':
            $generate_cache = TRUE; break;
        case 'd':
            $path = safe_path($opts['d']); break;
        case 't':
            $filetype = $opts['t']; break;
        case 'o':
            $overwrite_existing_files = TRUE; break;
        case 'h':
            $print_help_message = TRUE; break;
        case 'v':
            $verbose = TRUE; break;
        default:
            break;
    }

    if ( $generate_cache && $path && $filetype ) {
        if ($verbose) { print "\nCache generation starting...\n\n"; }
        generate_all_cache_files($path, $filetype, $overwrite_existing_files, $verbose);
    }
    else {
        print "
Command line usage:
    php bpg-srv.php -g -d \"path\" -t [photo|video|audio|all] [-o] [-v] [-h]

You need to run this as a user that can overwrite existing cache files.
    -g generate cache files
    -d directory to process (use \"all\" to process everything)
    -t type of file to process. photo, video, audio, or all.
    -o overwrite existing cache files
    -v verbose output
    -h display this help message

";
        exit(1);
    }
    exit(0);
}


//------------------------------------------------------------
// Web request handling
//------------------------------------------------------------

$path = '';
$file = '';
$view = 'default';
if (isset($_REQUEST['dir']))     $path   = safe_path($_REQUEST['dir']);
if (isset($_REQUEST['file']))    $file   = safe_path($_REQUEST['file']);
if (isset($_REQUEST['view']))    $view   = $_REQUEST['view'];
if (isset($_REQUEST['width']))   $width  = $_REQUEST['width'];
if (isset($_REQUEST['height']))  $height = $_REQUEST['height'];


if (isset($_REQUEST['setup']) || !file_exists("index.html")) {
    do_initial_setup_help();
    exit;
}


// When run by the web server choose an execution route based on _GET URL variable 'view'
if ( $view == 'rss' ) { // do an rss feed for these images
    do_rss( $path );
}
elseif ( $view == 'rssnew' ) { // do an rss feed for these images
    most_recent_photo_folder_rss();
}
elseif ( $view == 'rsspub' ) { // do an rss feed for these images
    do_rss_announcement();
}
elseif ( $view == 'ajax' ) { // respond to the XMLHttpRequest
    $cmd = '';
    $dir = '';
    $filepath = '';
    $comment = '';
    $direction = '';
    if (isset($_REQUEST['cmd']))        $cmd       = $_REQUEST['cmd'];
    if (isset($_REQUEST['dir']))        $dir       = urldecode( safe_path($_REQUEST['dir']) );
    if (isset($_REQUEST['filepath']))   $filepath  = urldecode( safe_path($_REQUEST['filepath']) );
    if (isset($_REQUEST['comment']))    $comment   = stripslashes($_REQUEST['comment']);
    if (isset($_REQUEST['direction']))  $direction = stripslashes($_REQUEST['direction']);

    switch ($cmd) {
        case 'get_thumbs':
            print render_thumbnail_area_html( get_list_of_files_to_show($dir), $dir ); break;
        case 'get_settings':
            ajaxJsonResponse( getUiSettings() ); break;
        case 'add_to_cart':
            ajaxJsonResponse( cartAddItem( $filepath ) ); break;
        case 'remove_from_cart':
            ajaxJsonResponse( cartRemoveItem( $filepath ) ); break;
        case 'empty_cart':
            ajaxJsonResponse( cartRemoveAll() ); break;
        case 'add_all_to_cart':
            ajaxJsonResponse( cartAddAll( $path ) ); break;
        case 'get_cart':
            ajaxJsonResponse( getCart() ); break;
        case 'get_site_stats':
            ajaxJsonResponse( getSiteStats() ); break;
        case 'write_comment':
            ajaxJsonResponse( writeExifTag( $filepath, 'Comment', $comment) ); break;
        case 'image_rotate':
            ajaxJsonResponse( ajaxImageRotate( $filepath, $direction ) ); break;
        case 'delete_file':
            ajaxJsonResponse( deleteOriginalFile( $filepath ) ); break;
        case 'buy_prints':
            get_shutterfly_buyprints_form(); break;
        default:
            print "error: that is not a recognized command."; break;
    }

}
elseif ( $view == 'download' ) { // download the cart in a zip file
    if (isset($_REQUEST['size']) && $_REQUEST['size'] == 'medium') {
        do_download_zipped_cart('medium');
    }
    else {
        do_download_zipped_cart('original');
    }
}
elseif ( $view == 'buyprints' ) { // buy prints from a lab
    do_shutterfly_buyprints();
}
else {
    print $html_page_top;
    print render_thumbnail_area_html( get_list_of_files_to_show($path), $path );
    print "<script>window.location = '.';</script>";
    print $html_page_bottom;
}

?>
