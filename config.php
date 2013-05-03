<?php

// The real path on the server to the original photos
// it can be relative to this bpg.php script or absolute.
$original_photos_dir = 'photos'; // (no trailing slash)

// The name of your gallery
$gallery_name = 'Picture Gallery';

// The name to put in the copyright tag of the RSS feeds
$copyright_owner_name = '';

// The name to use for the top level breadcrumb and the RSS feed.
// It will be linked to the parent directory of this script.
$home_site_name = 'My Website';

// To edit jpeg comments or delete the original files the user name must be in this $admin_users array
// (the original files must be writable by the webserver for this to work).
// The user name is identified by the by the web server's basic auth (.htaccess), so you'll need to use
// Apache's htpasswd and .htaccess files to make these users login
$admin_users = array(); // example: array( 'alice', 'bob' )

// The google analytics id code to use, if you want to enable it
//$google_analytics_tracking_code = '';

// The URL to the AWStats js file, if you want to enable it
//$awstats_url = '';

/*
 * Read the beginning of the bpg-srv.php file to see more setting that you can configure here.
 */
?>
