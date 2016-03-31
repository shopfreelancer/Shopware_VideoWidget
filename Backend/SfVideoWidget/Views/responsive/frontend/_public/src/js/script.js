;(function($,StateManager,  window) {
    'use strict';

    /**
     * Plugin to enable responsive modal window for vimeo and youtube videos
     *
     */
    $.plugin('sfMedia', {
        defaults: {
            video_width: "1600px",
            video_height: "900px"
        },
        init: function()
        {

            var me = this,
                $el = me.$el,
                opts = me.opts;


            $el.find('a').on('click', function(e)
            {
                e.preventDefault();

                /**
                 * if link is empty prevent further action
                 */
                var url = $el.find('.sf_media_video_link').attr('href');

                if (url == 0 || url.indexOf("<iframe") > -1) {
                    return;
                }


                var youTubeUrl = me.getYoutubeUrl(url);
                var vimeoUrl = me.getVimeoUrl(url);


                if (youTubeUrl === false && vimeoUrl === false) {
                    return;
                }

                if (youTubeUrl !== false) {
                    url = youTubeUrl;
                } else
                    if (vimeoUrl !== false) {
                        url = vimeoUrl;
                    }

                $.modal.open(me.createModalContent(url), {
                    mode: 'local',
                    sizing: 'auto',
                    width: opts.video_width,
                    height: opts.video_height

                });
            });

        },
        /**
         * return html for iframe
         * @param url
         * @returns {string}
         */
        createModalContent : function(url){
            var me = this,
                opts = me.opts;

            var content = '<div class="responsive-video"><iframe src="' + url + '" width="' +
                          opts.video_width +
                          '" height="' + opts.video_height +
                          '" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div>';
            return content;
        },
        /**
         *
         * convert video URLs to iframe compatible URLs - otherwise cross domain error
         * we get https://www.youtube.com/watch?v=90Yfmwwm7oU&feature=youtu.be and want https://www.youtube.com/embed/90Yfmwwm7oU
         *
         * @param url
         * @returns {string}
         */
        getYoutubeUrl: function(url)
        {
            var match = url.match(/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/);

            if (match && match[2].length == 11) {
                return "https://www.youtube.com/embed/" + match[2];
            } else {
                return false;
            }
        },
        /**
         * convert video URLs to iframe compatible URLs - otherwise cross domain error
         * we get https://vimeo.com/42487034 and want https://player.vimeo.com/video/42487034
         *
         * @param url
         * @returns {string}
         */
        getVimeoUrl: function(url)
        {
            var match = url.match(/^.*(vimeo.com\/)([0-9]+).*/);

            if (match) {
                return "https://player.vimeo.com/video/" + match[2]
            } else {
                return false;
            }
        }

    })

})(jQuery, StateManager, window);

$.subscribe('plugin/swEmotion/onInitElements', function()
{
    if ($('.sf_media_element').length > 0) {
        $('.sf_media_element').each(function()
        {
            $(this).sfMedia();
        });
    }
});
