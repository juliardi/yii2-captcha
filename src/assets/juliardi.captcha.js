/**
 * Juliardi Captcha widget.
 *
 * This is the JavaScript widget used by the juliardi\captcha\Captcha widget.
 */
(function ($) {
    $.fn.juliardiCaptcha = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist in jQuery.juliardiCaptcha');
            return false;
        }
    };

    var defaults = {
        refreshUrl: undefined,
        hashKey: undefined
    };

    var methods = {
        init: function (options) {
            return this.each(function () {
                var $e = $(this);
                var settings = $.extend({}, defaults, options || {});
                $e.data('juliardiCaptcha', {
                    settings: settings
                });

                $e.on('click.juliardiCaptcha', function () {
                    methods.refresh.apply($e);
                    return false;
                });
            });
        },

        refresh: function () {
            var $e = this,
                settings = this.data('juliardiCaptcha').settings;
            $.ajax({
                url: $e.data('juliardiCaptcha').settings.refreshUrl,
                dataType: 'json',
                cache: false,
                success: function (data) {
                    $e.attr('src', data.url);
                    $('body').data(settings.hashKey, [data.hash1, data.hash2]);
                }
            });
        },

        destroy: function () {
            this.off('.juliardiCaptcha');
            this.removeData('juliardiCaptcha');
            return this;
        },

        data: function () {
            return this.data('juliardiCaptcha');
        }
    };
})(window.jQuery);
