BPG - Ben's Picture Gallery
====
> A simple gallery program to show pictures and video from your digital camera on your website.

Demo
------
View the live [demo here](https://benroy73.github.io/bpg/)

Installation
----------
1. Download and unzip this to your web server.
2. Edit the `config.php` file with your own settings.
3. View `bpg-srv.php?setup` in a browser and install any missing dependencies.
4. Put your pictures and videos in the `photos` directory.
5. schedule a cron task to generate any missing cache files and clean up the cache
    `0 1 * * * cd /var/www/photos && php bpg-srv.php -g -d "all" -t all`
6. if you don't want to use PHP to server the content on the web server (like the demo here) then edit the `bpg.js` file uncomment the line with `static_mode_active = true`


Dependencies
---------
* Apache web server (tested on Mac, Ubuntu and Debian)
    * `sudo apt-get install apache2`
* PHP with GD and EXIF support
    * `sudo apt-get install php5 php5-gd php5-cli`
* ffmpeg and qt-faststart (for resizing videos)
    * [Ubuntu install guide](http://ubuntuforums.org/showthread.php?t=786095)
* exiftool (for editing JPEG file comments)
    * `sudo apt-get install libimage-exiftool-perl`
* zip (for downloading files)
    * `sudo apt-get install zip`


Features
-----------
* Supports jpg, mp4, mov, avi, 3gp, wav, and mp3 files.
* Slideshow that shows both photos and videos.
* Pictures automatically scale to fit any screen size.
* Automatic image rotation from the EXIF orientation data in the photo jpg files.
* Captions can be added to photos and are saved in the original jpeg file.
   (This means you can keep your captions if you want to use a different program in the future)
* Editing captions, manual rotation, and deleting files is restricted to "admin" users.
* Tested with cameras from Canon, Nikon, Kodak and iPhone or Android phones
* Automatically maintained cache of thumbnails and small, medium and large versions of the photos.
* Original photos can be protected from downloading.
* Watermarks can be added to the resized images.
* Videos are converted to the h264 format for low bandwidth.
* Videos can be played on nearly any phone, tablet or computer thanks to MediaElement.
* Load balanced mirror feature can be used for faster thumbnail loading where local bandwidth is limited.
* RSS feed of the latest photos.
* RSS announcement feed can be used to annnounce new photos.
* Shopping cart for download a zip file or purchasing prints.
* Most features can be enabled or disabled individually.
* Built-in installation help guide.


Credits
-------
> Thanks to everyone who made this possible

* [jQuery](http://jquery.com/)
* [Bootstrap](http://twitter.github.io/bootstrap)
* [mediaelement](https://github.com/benroy73/mediaelement)
* [PhotoSwipe](https://github.com/benroy73/PhotoSwipe)
* [exiftool](http://www.sno.phy.queensu.ca/~phil/exiftool/)
* cart.gif from [http://paularmstrongdesigns.com/projects/gallery-2-icons/](http://paularmstrongdesigns.com/projects/gallery-2-icons/)
* loading animations from [http://www.ajaxload.info](http://www.ajaxload.info/)
* sound-icon.png from [http://en.wikipedia.org/wiki/File:Sound-icon.svg](http://en.wikipedia.org/wiki/File:Sound-icon.svg)
