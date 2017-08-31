'use strict'

import $ from 'jquery'
import 'bootstrap'
import hljs from 'highlight.js'

hljs.initHighlightingOnLoad()

$(document).ready(function () {
  let menu = $('.menu')
  let menuOffset = menu.offset()
  $(window).on('scroll', function () {
    if ($(window).scrollTop() > menuOffset.top) {
      menu.addClass('sticky')
    } else {
      menu.removeClass('sticky')
    }
  })
  menu.wrap('<div class="menu-placeholder"></div>')
  $('.menu-placeholder').height(menu.outerHeight())
})
