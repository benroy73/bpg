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
 */

/*
Known issues:
    Android video playing
        Android won't do basic HTTP auth for playing video
            solution use cookie auth instead
        positioning video modal box on android phone

    PhotoSwipe
        debug video photoswipe issues (existed in old version so it's not bootstrap's fault)
            FF crashes when pausing video
            playing video and switching back to photoswipe causes photoswipe to loose track of some videos posters

*/

var SRV_URL = 'bpg-srv.php',
    photoSwipeClickEvent,
    cart_item_count = 0,
    orig_dl_time = '',
    large_dl_time = '',
    has_admin_permissions = false,
    static_mode_active = false;

static_mode_active = true; //for use without php on the web server

function gup(url, name) {
    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
    var regexS = "[\\?&]" + name + "=([^&#]*)",
        regex = new RegExp(regexS),
        results = regex.exec(url);
    if (results === null) {
        return '';
    }
    else {
        return results[1];
    }
}

function isCartPage() {
    if (gup(location.href, 'dir') === 'cart') {
        return true;
    }
    else {
        return false;
    }
}

function isTopPage() {
    if (gup(location.href, 'dir') === '') {
        return true;
    }
    else {
        return false;
    }
}

function displaySiteStats() {
    $('div#content').append("<span id='stats'></span>");
    if (static_mode_active) {
        $('#site_stats').load('_cache/stats');
        return;
    }

    $.ajax({
        type: 'GET',
        url: SRV_URL,
        data: {cmd: 'get_site_stats', view: 'ajax'},
        success: function (data, textStatus) {
            $('#site_stats').text(data);
        }
    });
}

//function getExif(filepath) {
//    $.ajax({
//        type: 'POST',
//        url: '?view=ajax',
//        dataType: 'json',
//        data: 'cmd=get_exif&filepath=' + filepath,
//        success: function (data, textStatus) {
//            var name,
//                info = '';
//            for (name in data) {
//                info = info + name + ': ' + data[name] + "\n";
//            }
//            alert(info);
//        }
//    });
//}

function getUiSettings() {
    // first try to get the settings from the dynamic php service, if that fails get it from the static settings.json file
    if (static_mode_active) {
        return;
    }
    $.ajax({
        type: 'GET',
        url: SRV_URL,
        data: {cmd: 'get_settings', view: 'ajax'},
        dataType: 'json',
        success: function (data, textStatus) {
            has_admin_permissions = data.isAdmin;
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            static_mode_active = true;
        },
        complete: function () {
            if (has_admin_permissions === true) {
                $('#ui_admin_button').show();
            }
            else {
                $('#ui_admin_button').hide();
            }
        }
    });
}

function adminImageRotate(filepath, direction) {
    // submit the change to the server for saving
    $.ajax({
        type: 'POST',
        url: SRV_URL,
        data: {cmd: 'image_rotate', filepath: filepath, direction: direction, view: 'ajax'},
        success: function (data, textStatus) {
            window.location.reload();
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert("Error: " + textStatus + "\n" + errorThrown);
        }
    });
}

function adminSaveComment(filepath, value) {
    // submit the change to the server for saving
    $.ajax({
        type: 'POST',
        url: SRV_URL,
        data: {cmd: 'write_comment', filepath: filepath, comment: value, view: 'ajax'},
        success: function (data, textStatus) {
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert("Error: " + textStatus + "\n" + errorThrown);
        }
    });
    return (value);
}

function adminDeleteFile(filepath, obj) {
    if (confirm('Is it really OK to delete this file?')) {
        // submit the change to the server for saving
        $.ajax({
            type: 'POST',
            url: SRV_URL,
            data: {cmd: 'delete_file', filepath: filepath, view: 'ajax'},
            success: function (data, textStatus) {
                // replace the image with a deleted placeholder
                $(obj).html("<a><span class='deleted_file'>Deleted</span></a><div class='thumb_comments'></div>");
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert("Error: " + textStatus + "\n" + errorThrown);
            }
        });
    }
}

function cartAddAll(path) {
    $.ajax({
        type: 'POST',
        url: SRV_URL,
        data: {cmd: 'add_all_to_cart', dir: path, view: 'ajax'},
        success: function (data, textStatus) {
            updateCartButtons();
        }
    });
}

function cartRemoveAll() {
    $.ajax({
        type: 'POST',
        url: SRV_URL,
        data: {cmd: 'empty_cart', view: 'ajax'},
        success: function (data, textStatus) {
            updateCartButtons();
            if (isCartPage()) {
                $('#media li').css('opacity', '0.25');
            }
        }
    });
}
function cartAddItem(filepath, obj) {
    $.ajax({
        type: 'POST',
        url: SRV_URL,
        data: {cmd: 'add_to_cart', filepath: filepath, view: 'ajax'},
        success: function (data, textStatus) {
            updateCartButtons();
        }
    });
    if (isCartPage()) {
        $(obj).css('opacity', '1');
    }
}
function cartRemoveItem(filepath, obj) {
    $.ajax({
        type: 'POST',
        url: SRV_URL,
        data: {cmd: 'remove_from_cart', filepath: filepath, view: 'ajax'},
        success: function (data, textStatus) {
            updateCartButtons();
        }
    });
    if (isCartPage()) {
        $(obj).css('opacity', '0.25');
    }
}

function updateCartButtons() {
    var shopping_cart = [];
    // get the cart and process each item to update any buttons on this page
    if (static_mode_active) {
        return;
    }

    $('#ui_cart_menu').show();

    $.ajax({
        type: 'GET',
        url: SRV_URL,
        dataType: 'json',
        data: {cmd: 'get_cart', view: 'ajax'},
        success: function (cart) {
            shopping_cart = cart.file;

            // set the state of each cart button
            cart_item_count = 0;
            $('#media li').each(function () {
                var obj = this,
                    filepath = $(this).children('a:first').attr('href');
                if (typeof(filepath) === 'undefined') {
                    filepath = $(this).children('img:first').attr('src');
                    if (typeof(filepath) === 'undefined') {
                        return;
                    }
                }

// this doesn't work for videos since the file name is changed from the original

                filepath = filepath.replace(/^.*_cache\/_small\/|^.*_cache\/_medium\/|^.*_cache\/_large\//g, '')
                filepath = decodeURIComponent(filepath);

                $(this).children('.cartcontrol').unbind('click');
                if ($.inArray(filepath, shopping_cart) > -1) { // in the cart
                    $(this).children('.cartcontrol')
                        .text('Remove from cart')
                        .click(function (event) {
                            $(this).html("<img src='images/loading.gif' width='50%' height='50%'>");
                            cartRemoveItem(filepath, obj);
                    });
                    $(this).find('.cart_icon').show();
                    cart_item_count += 1;
                }
                else { // not in the cart
                    $(this).children('.cartcontrol')
                        .text('Add to cart')
                        .click(function (event) {
                            $(this).html("<img src='images/loading.gif' width='50%' height='50%'>");
                            cartAddItem(filepath, obj);
                    });
                    $(this).find('.cart_icon').hide();
                }
            });

            if (isCartPage()) {
                $('#cart_item_count').text('You have ' + cart_item_count + ' items in your cart.');
                if (cart_item_count > 0) {
                    $('#buy_prints_menu_option, #download_files_menu_option').parent().removeClass('disabled');
                }
                else {
                    $('#buy_prints_menu_option, #download_files_menu_option').parent().addClass('disabled');
                }
            }
            else {
                $('#buy_prints_menu_option, #download_files_menu_option').parent().addClass('disabled');
            }

        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
             //alert( "Error: " + textStatus +"\n"+ errorThrown );
        }
    });
}


function initializeCartMenu() {

    if (static_mode_active) {
        return;
    }

    $('#remove_all_from_cart').click( function (event) {
        event.preventDefault();
        cartRemoveAll();
    });

    if (isCartPage()) {
        $('#add_some_to_cart, #add_all_to_cart').hide();
        $('#remove_some_from_cart, #remove_all_from_cart').show();
        $('#view_cart_menu_option').parent().addClass('disabled');
        $('#buy_prints_menu_option, #download_files_menu_option').parent().removeClass('disabled');

        $('#remove_some_from_cart').click( function(event){
            event.preventDefault();
            if ($(this).text() != 'Remove some pictures') {
                $(this).text('Remove some pictures');
                $('.cartcontrol').hide();
            }
            else {
                $(this).text('Done removing pictures');
                $('.cartcontrol').show();
            }
        });

        $('#buy_prints_menu_option').click( function (event) {
            event.preventDefault();
            if (!$(this).parent().hasClass('disabled')){
                $.ajax({
                    type: 'POST',
                    url: SRV_URL,
                    data: {cmd: 'buy_prints', view: 'ajax'},
                    success: function (data, textStatus) {
                        $('#content_media').html(data);
                    }
                });
            }
        });

        $('#download_files_menu_option').click( function (event) {
            event.preventDefault();
            if (!$(this).parent().hasClass('disabled')){
                window.location = SRV_URL + '?view=download';
            }
        });

    }
    else {
        $('#add_some_from_cart, #add_all_from_cart').show();
        $('#remove_some_from_cart, #remove_all_from_cart').hide();
        $('#view_cart_menu_option').parent().removeClass('disabled');
        $('#buy_prints_menu_option, #download_files_menu_option').parent().addClass('disabled');

        $('#add_some_to_cart').click( function (event){
            event.preventDefault();
            if ($(this).text() != 'Add some pictures') {
                $(this).text('Add some pictures');
                $('.cartcontrol').hide();
            }
            else {
                $(this).text('Done adding pictures');
                $('.cartcontrol').show();
            }
        });

        $('#add_all_to_cart').click( function (event) {
            event.preventDefault();
            cartAddAll(gup(location.href, 'dir'));
        });
    }
}


function setupAdminControls() {
    var mediaItems = $('#media li');

    // add or remove the admin controls
    $('#admin_mode_button').click( function (event) {
        event.preventDefault();
        if (isCartPage()) {
            return;
        }

        if (!$(this).hasClass('admin_mode_active')) {
            $(this).addClass('admin_mode_active');

            // add admin delete & edit buttons
            mediaItems.each(function () {
                var obj = this,
                    file_ext = $(this).attr('id').substr($(this).attr('id').length - 3, 3),
                    filepath = $(this).children('a:first').attr('href')
                                .replace(/^.*_cache\/_small\/|^.*_cache\/_medium\/|^.*_cache\/_large\//g, '')
                                .replace(/mp4$/, file_ext);

                $(this).append("<a class='admin_delete_file btn' title='Permanently delete this file'>delete</a>");
                $(this).children('.admin_delete_file').unbind('click').click( function (event) {
                        adminDeleteFile(filepath, obj);
                    });

                if (file_ext === 'JPG') {
                    $(this).append("<a class='btn admin_image_rotate_ccw' title='Rotate Counter-Clockwise'><img src='images/rotate_ccw.png' width='50%' height='50%' alt='Rotate Counter-Clockwise'></a>");
                    $(this).children('.admin_image_rotate_ccw').unbind('click').click( function (event) {
                        $(this).parent().children('a:first').html("<img src='images/loading.gif'>");
                        $(this).parent().children('.btn').remove();
                        adminImageRotate(filepath, 'ccw');
                    });
                    $(this).append("<a class='btn admin_image_rotate_cw' title='Rotate Clockwise'><img src='images/rotate_cw.png' width='50%' height='50%' alt='Rotate Clockwise'></a>");
                    $(this).children('.admin_image_rotate_cw').unbind('click').click( function (event) {
                        $(this).parent().children('a:first').html("<img src='images/loading.gif'>");
                        $(this).parent().children('.btn').remove();
                        adminImageRotate(filepath, 'cw');
                    });
                }

                // make the jpeg comment editable
                $(this).children('.thumb_comments').editable(
                    function (value, settings) {
                        adminSaveComment(filepath, value);
                        return (value);
                    },
                    {
                    placeholder : '',
                    indicator   : 'Saving...',
                    cancel      : 'Cancel',
                    submit      : 'Save',
                    tooltip     : 'Click to edit...'
                })
            });
            $('.thumb_comments').css('border', '1px dashed');


            $(mediaItems).addClass('admin_mode_active');
            $(mediaItems).addClass('thumbnail');

            $('#media a.slide').unbind('click').bind('click', function(){return false;}); //disable photoswipe
        }
        else {
            $(this).removeClass('admin_mode_active');
            $('.thumb_comments').css('border', '');

            // remove admin buttons
            $(mediaItems).removeClass('admin_mode_active');
            $(mediaItems).removeClass('thumbnail');

            mediaItems.each(function () {
                $(this).children('.admin_delete_file').remove();
                $(this).children('.admin_image_rotate_ccw').remove();
                $(this).children('.admin_image_rotate_cw').remove();
                // make the jpeg comment not editable
                $(this).children('.thumb_comments').unbind('click').removeAttr('title');
            });

//            $('#media a.slide').unbind('click').bind('click', photoSwipeClickEvent); //re-enable photoswipe

        }
    });
}


function playPhotoSwipeVideo(video_url) {
    var width, height, video_html;

    if (video_url.search(/.mov|.mp4|.mp3/i) > -1) { // the video formats that the players can handle
        width = photoSwipeInstance.getCurrentImage().imageEl.width;
        height = photoSwipeInstance.getCurrentImage().imageEl.height;

        //video_width = photoSwipeInstance.getCurrentImage().imageEl.naturalWidth;
        //video_height = photoSwipeInstance.getCurrentImage().imageEl.naturalHeight;

        //console.log(video_height + " < " + $(window).height() + " && " + video_width + " < " + $(window).width());

        //// see if the video size can scale up
        //if (video_height < $(window).height() && video_width < $(window).width()) {
        //    //if (video_height * 2 < $(window).height() && video_width * 2 < $(window).width()) { // room to double the size
        //    //    width = video_width * 2;
        //    //    height = video_height * 2;
        //    //}
        //    //else
        //    if (video_height * 1.5 < $(window).height() && video_width * 1.5 < $(window).width()) { // increasing to 1.5 size
        //        width = video_width * 1.5;
        //        height = video_height * 1.5;
        //    }
        //}

//style="width: 100%; height: 100%; z-index: 4001;"

        video_html = '<video src="' + video_url + '" width="' + width + '" height="' + height + '" poster="' + video_url + '.jpg" autoplay controls></video>';
        //console.log(video_html);

        $('body').removeClass('ps-active');

        $('#slideshow_screen').html(video_html);
        $(window.document).unbind('keydown', photoSwipeInstance.keyDownHandler)
        $('#videoPlayerModal').modal();

        // position centered horizontally and vertically
        $('#videoPlayerModal .modal-body').css({'width': width + 'px', 'height': height + 'px'});
        $('#videoPlayerModal').css({'width': (width + 30) + 'px',
                                    'top': '0',
                                    'margin-top': (($(window).height() - height) / 2) + 'px',
                                    'margin-left': '-' + ((width + 30)/2) + 'px'
                                    });

        $('#videoPlayerModal').on('hidden', function () {
            //console.log('videoPlayerModal "hidden" event');
            $('body').addClass('ps-active');
            photoSwipeInstance.toolbar.clearTimeout();
            photoSwipeInstance.toolbar.showToolbar();
            $(window.document).bind('keydown', photoSwipeInstance.keyDownHandler);
            $('#slideshow_screen').html('');
        })

        var videos = $('video,audio').mediaelementplayer({
           success: function(media) {
                media.addEventListener('ended', function() {
                    //media.pause();
                    //console.log('mediaelementplayer "ended" event');
                    $('#videoPlayerModal').modal('hide');
                    $('body').addClass('ps-active');
                    photoSwipeInstance.toolbar.clearTimeout();
                    photoSwipeInstance.toolbar.showToolbar();
                    $(window.document).bind('keydown', photoSwipeInstance.keyDownHandler);
                }, true);
            }
        });

    }
}

function setupPhotoSwipe() {
    if ($("#media a.slide").length === 0) {
        return;
    }

    // using Photo Swipe from http://www.photoswipe.com/
    var options = {
            //target: $('#photoswipe_layer'),
            zIndex: 7300,
            enableMouseWheel: false,
            captionAndToolbarAutoHideDelay: 3000,
            loop: false,
            imageScaleMethod: 'fitNoUpscale',
            swipeThreshold: 40,
            allowUserZoom: false,
            getImageCaption: function(el){
                var captionText, captionEl;
                // Get the caption from the alt tag
                if (el.nodeName === "IMG"){
                    captionText = $(el).parent().children('.thumb_comments').text();
                }
                var i, j, childEl;
                for (i=0, j=el.childNodes.length; i<j; i++){
                    childEl = el.childNodes[i];
                    if (el.childNodes[i].nodeName === 'IMG'){
                        captionText = $(childEl).parent().parent().children('.thumb_comments').text();
                    }
                }
                return captionText;
            },
            getImageSource: function(el) {
                if (el.href.search(/.mov$|.mp4$|.mp3$/i) > -1) {
                    // this is where the video poster image is located
                    return el.href + '.jpg';
                }
                else {
                    // THIS IS THE PLACE WHERE ADAPTIVE IMAGE SIZES CAN BE RETURNED
                    return el.href;
                }
            },
            getImageMetaData: function(el){
                return {
                    href: el.getAttribute('href')
                }
            }

        };

	photoSwipeInstance = $("#media a.slide").photoSwipe(options);
    PhotoSwipe = window.Code.PhotoSwipe;

    // save the photoswipe click event so it can be toggled in the admin controls
    //photoSwipeClickEvent = $("#media a.slide").data("events").click[0];

    // onTouch
    photoSwipeInstance.addEventHandler(window.Code.PhotoSwipe.EventTypes.onTouch, function(e){
        if (e.action === 'tap'){
            if (photoSwipeInstance.getCurrentImage().metaData.href.search(/.mov$|.mp4$|.mp3$/i) > -1) {
                playPhotoSwipeVideo(photoSwipeInstance.getCurrentImage().metaData.href);
                photoSwipeInstance.toolbar.clearTimeout();
                photoSwipeInstance.toolbar.showToolbar();
            }
        }
    });

    // onDisplayImage event could be use instead of tap to play a video automatically
    photoSwipeInstance.addEventHandler(PhotoSwipe.EventTypes.onDisplayImage, function(e){
        // close any open video dialog
        $('#videoPlayerModal').modal('hide');

        if (photoSwipeInstance.getCurrentImage().metaData.href.search(/.mov$|.mp4$|.mp3$/i) > -1) {
            // need to keep the toolbar open on video images
            photoSwipeInstance.toolbar.clearTimeout();
            photoSwipeInstance.toolbar.showToolbar();
        }
        else { // just a normal jpg image
            photoSwipeInstance.toolbar.setTimeout();
        }
    });

    // onResetPosition
    //photoSwipeInstance.addEventHandler(PhotoSwipe.EventTypes.onResetPosition, function(e){
    //    // the screen size has changed, so the layout may need adjusting
    //    //$('.mejs-container, .ps-carousel-item video[src="' + photoSwipeInstance.getCurrentImage().metaData.href + '"]')
    //    //    .attr('style', $('.ps-carousel-item img[src="' + photoSwipeInstance.getCurrentImage().imageEl.originalSrc + '"]')
    //    //    .attr('style'));
    //});

    // onHide
    photoSwipeInstance.addEventHandler(PhotoSwipe.EventTypes.onHide, function(e){
        // scroll to the image that was last viewed
        //console.log(photoSwipeInstance.getCurrentImage().metaData.href);
        $('html, body').animate({ scrollTop: $('a[href="'+ photoSwipeInstance.getCurrentImage().metaData.href +'"]').offset().top - 100 }, 1);
    });

    // onShow
    //photoSwipeInstance.addEventHandler(PhotoSwipe.EventTypes.onShow, function(e){
    //    // Safari needs some help to get the position right on long pages
    //    //photoSwipeInstance.carousel.resetPosition();
    //    //console.log("onShow resetPosition");
    //});

    $(document).on('mousemove', 'div.ps-uilayer', function() {
        photoSwipeInstance.toolbar.showToolbar();
        photoSwipeInstance.toolbar.showCaption()
        photoSwipeInstance.toolbar.setTimeout();
    });
}

function postContentLoadedInit() {
    var dir = gup(location.href, 'dir'),
        rss_link = SRV_URL + '?view=rss&amp;dir=' + dir;

    if ($('#media li').size() > 0) {
        $('#add_some_to_cart, #add_all_to_cart, #remove_some_from_cart, #remove_all_from_cart').parent().removeClass('disabled');
        $('head').append('<link type="application/rss+xml" rel="alternate" href="' + rss_link + '" title="' + dir + ' photocast"/>');
    }
    else {
        $('#add_some_to_cart, #add_all_to_cart, #remove_some_from_cart, #remove_all_from_cart').parent().addClass('disabled');
    }
    if ($('#folders li').size() === 0 && $('#media li').size() === 0) {
        $('#content_media').html('<ol class="nav nav-tabs nav-stacked"><li>Empty</li></ol>');
        return;
    }

    if (isTopPage()) {
        displaySiteStats();
    }

    if ($('#media li').size() === 0) {
        return
    }

    // adaptive sizing for videos needs to be done manually here
    if ( $(window).width() < 640 ) {
        $('#media a.slide').each(function() {
            $(this).attr('href', $(this).attr('href').replace(/\/_medium\/|\/_large\//, '/_small/'));
        });
    }
    else if ( $(window).width() < 1920 ) {
        $('#media a.slide').each(function() {
            $(this).attr('href', $(this).attr('href').replace(/\/_small\/|\/_large\//, '/_medium/'));
        });
    }


    $("#media a.slide").append("<img class='cart_icon' src='images/cart.gif'>");
    $('#media li').append("<button class='cartcontrol btn'>Add to cart</button>");
    updateCartButtons();

    setupAdminControls();

    setupPhotoSwipe();
    //(function() {
    //    var node_me = document.createElement('script'); node_me.type = 'text/javascript'; node_me.async = true;
    //    node_me.src = 'mediaelement-2.6.5-bpg-fixes/mediaelement-and-player.min.js';
    //
    //    var node_pswk = document.createElement('script'); node_pswk.type = 'text/javascript'; node_pswk.async = true;
    //    node_pswk.src = 'photoswipe-3.0.4/lib/klass.min.js';
    //
    //    var node_psw = document.createElement('script'); node_psw.type = 'text/javascript'; node_psw.async = true;
    //    node_psw.src = 'photoswipe-3.0.4/code.photoswipe.jquery-3.0.4.min.js';
    //
    //    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(node_psw, s);
    //    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(node_pswk, s);
    //    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(node_me, s);
    //
    //    (function(window, $, PhotoSwipe){
    //        setupPhotoSwipe();
    //    }(window, window.jQuery, window.Code.PhotoSwipe));
    //
    //})();

}

function staticModeContentLoad() {
    // if the php load fails switch to static mode in the client js
    $.ajax({
        type: 'GET',
        url: '_cache/_thumbnails/' + gup(location.href, 'dir') + '/thumbnails.html',
        success: function (data, textStatus) {
            $('#content_media').html(data);
            postContentLoadedInit();
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            $('#content_media').html('Error: ' + textStatus);
        }
    });
}

function asyncMediaContentLoad() {
    if (static_mode_active) {
        staticModeContentLoad();
        return;
    }

    $.ajax({
        type: 'GET',
        url: SRV_URL,
        data: {cmd: 'get_thumbs', view: 'ajax', dir: gup(location.href, 'dir')},
        success: function (data, textStatus) {
            $('#content_media').html(data);
            postContentLoadedInit();
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            static_mode_active = true;
            staticModeContentLoad(); // if the php load fails switch to static mode
        }
    });
}

function updateBreadcrumbs() {
    var crumbs = '',
        path = '';
    if (gup(location.href, 'dir') === '') {
        $('#top_level_breadcrumb').addClass('active');
        return;
    }
    $('#top_level_breadcrumb').removeClass('active');

    $.each(gup(location.href, 'dir').split('/'), function(i, value) {
        path += value;
        active_class = '';
        if (i === gup(location.href, 'dir').split('/').length - 1) {
            active_class = 'class="active"';
        }
        crumbs += '<li ' + active_class + '><a href="?dir=' + path + '">' + decodeURIComponent(value) + '</a></li>';
        path += '/';
    });
    $('#top_level_breadcrumb').after(crumbs);
}

function preContentLoadedInit() {
    getUiSettings();
    updateBreadcrumbs()
    initializeCartMenu();
}

//(function(window, $, PhotoSwipe){
$(document).ready(function () {
    preContentLoadedInit();
    asyncMediaContentLoad();

    // full screen browser
    if(navigator.userAgent.match(/Android/i)){
        window.scrollTo(0,1);
    }
});
//}(window, window.jQuery, window.Code.PhotoSwipe));
