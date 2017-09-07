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
  let passButton = document.getElementById('passbutton')
  if (document.body.contains(passButton)) {
    let passButton = document.getElementById('passbutton')
    let passInput = document.getElementById('passinput')
    passButton.style.display = 'block'
    passInput.style.display = 'none'
  }
})

let newPass = document.getElementById('newpass')
if (document.body.contains(newPass)) {
  newPass.addEventListener('click', () => {
    const pass = randPass()
    let pass1 = document.getElementById('appbundle_user_plainPassword_first')
    let pass2 = document.getElementById('appbundle_user_plainPassword_second')
    let passButton = document.getElementById('passbutton')
    let passInput = document.getElementById('passinput')
    passButton.style.display = 'none'
    passInput.style.display = 'block'
    pass1.value = pass
    pass2.value = pass
  }, false)
}

let cancelPass = document.getElementById('cancelpass')
if (document.body.contains(cancelPass)) {
  cancelPass.addEventListener('click', () => {
    let pass1 = document.getElementById('appbundle_user_plainPassword_first')
    let pass2 = document.getElementById('appbundle_user_plainPassword_second')
    let passButton = document.getElementById('passbutton')
    let passInput = document.getElementById('passinput')
    passButton.style.display = 'block'
    passInput.style.display = 'none'
    pass1.value = ''
    pass2.value = ''
  }, false)
}

let viewPass = document.getElementById('viewpass')
if (document.body.contains(viewPass)) {
  viewPass.addEventListener('click', () => {
    let pass1 = document.getElementById('appbundle_user_plainPassword_first')
    let pass2 = document.getElementById('appbundle_user_plainPassword_second')
    if (pass1.type === 'text') {
      pass1.type = 'password'
      pass2.type = 'password'
    } else {
      pass1.type = 'text'
      pass2.type = 'text'
    }
  }, false)
}

function randPass () {
  let length = 12
  let lower = 'abcdefghijklmnopqrstuvwxyz'
  let upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
  let num = '0123456789'
  let symbol = '![]{}()%&*$€#^<>~@|'
  let pass = ''
  for (let i = 0; i < length / 4; i++) {
    pass += lower.charAt(Math.floor(Math.random() * lower.length))
    pass += upper.charAt(Math.floor(Math.random() * upper.length))
    pass += num.charAt(Math.floor(Math.random() * num.length))
    pass += symbol.charAt(Math.floor(Math.random() * symbol.length))
  }
  return pass
}
