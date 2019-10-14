/* =============================================================
 * bootstrap-scrollspy.js v1.4.0
 * http://twitter.github.com/bootstrap/javascript.html#scrollspy
 * =============================================================
 * Copyright 2011 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================== */


!function ( $ ) {

  "use strict"

  var $window = $(window)

  function ScrollSpy( topbar, selector ) {
    var processScroll = $.proxy(this.processScroll, this)
    this.$topbar = $(topbar)
    this.selector = selector || 'li > a'
    this.refresh()
    this.$topbar.delegate(this.selector, 'click', processScroll)
    $window.scroll(processScroll)
    this.processScroll()
  }

  ScrollSpy.prototype = {

      refresh: function () {
        this.targets = this.$topbar.find(this.selector).map(function () {
          var href = $(this).attr('href')
          return /^#\w/.test(href) && $(href).length ? href : null
        })

        this.offsets = $.map(this.targets, function (id) {
          return $(id).offset().top
        })
      }

    , processScroll: function () {
        var scrollTop = $window.scrollTop() + 100
          , offsets = this.offsets
          , targets = this.targets
          , activeTarget = this.activeTarget
          , i

        for (i = offsets.length; i--;) {
          activeTarget != targets[i]
            && scrollTop >= offsets[i]
            && (!offsets[i + 1] || scrollTop <= offsets[i + 1])
            && this.activateButton( targets[i] )
        }
      }

    , activateButton: function (target) {
        this.activeTarget = target

        this.$topbar
          .find(this.selector).parent('.active')
          .removeClass('active')

        this.$topbar
          .find(this.selector + '[href="' + target + '"]')
          .parent('li')
          .addClass('active')
      }

  }

  /* SCROLLSPY PLUGIN DEFINITION
   * =========================== */

  $.fn.scrollSpy = function( options ) {
    var scrollspy = this.data('scrollspy')

    if (!scrollspy) {
      return this.each(function () {
        $(this).data('scrollspy', new ScrollSpy( this, options ))
      })
    }

    if ( options === true ) {
      return scrollspy
    }

    if ( typeof options == 'string' ) {
      scrollspy[options]()
    }

    return this
  }

  $(document).ready(function () {
    $('body').scrollSpy('[data-scrollspy] li > a')
  })

}( window.jQuery || window.ender );



/* ==========================================================
 * bootstrap-affix.js v2.2.2
 * http://twitter.github.com/bootstrap/javascript.html#affix
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

	"use strict"; // jshint ;_;


	/* AFFIX CLASS DEFINITION
	 * ====================== */

	var Affix = function (element, options) {
		this.options = $.extend({}, $.fn.affix.defaults, options)
		this.$window = $(window)
			.on('scroll.affix.data-api', $.proxy(this.checkPosition, this))
			.on('click.affix.data-api',  $.proxy(function () { setTimeout($.proxy(this.checkPosition, this), 1) }, this))
		this.$element = $(element)
		this.checkPosition()
	}

	Affix.prototype.checkPosition = function () {
		if (!this.$element.is(':visible')) return

		var scrollHeight = $(document).height()
			, scrollTop = this.$window.scrollTop()
			, position = this.$element.offset()
			, offset = this.options.offset
			, offsetBottom = offset.bottom
			, offsetTop = offset.top
			, reset = 'affix affix-top affix-bottom'
			, affix

		if (typeof offset != 'object') offsetBottom = offsetTop = offset
		if (typeof offsetTop == 'function') offsetTop = offset.top()
		if (typeof offsetBottom == 'function') offsetBottom = offset.bottom()

		affix = this.unpin != null && (scrollTop + this.unpin <= position.top) ?
			false    : offsetBottom != null && (position.top + this.$element.height() >= scrollHeight - offsetBottom) ?
			'bottom' : offsetTop != null && scrollTop <= offsetTop ?
			'top'    : false

		if (this.affixed === affix) return

		this.affixed = affix
		this.unpin = affix == 'bottom' ? position.top - scrollTop : null

		this.$element.removeClass(reset).addClass('affix' + (affix ? '-' + affix : ''))
	}


	/* AFFIX PLUGIN DEFINITION
	 * ======================= */

	$.fn.affix = function (option) {
		return this.each(function () {
			var $this = $(this)
				, data = $this.data('affix')
				, options = typeof option == 'object' && option
			if (!data) $this.data('affix', (data = new Affix(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	$.fn.affix.Constructor = Affix

	$.fn.affix.defaults = {
		offset: 0
	}


	/* AFFIX DATA-API
	 * ============== */

	$(window).on('load', function () {
		$('[data-spy="affix"]').each(function () {
			var $spy = $(this)
				, data = $spy.data()

			data.offset = data.offset || {}

			data.offsetBottom && (data.offset.bottom = data.offsetBottom)
			data.offsetTop && (data.offset.top = data.offsetTop)

			$spy.affix(data)
		})
	})


}(window.jQuery);