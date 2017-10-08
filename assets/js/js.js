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
  checkCookies()
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

let replyButton = document.querySelectorAll('.reply-button')
if (replyButton) {
  for (let i = 0; i < replyButton.length; i++) {
    replyButton[i].addEventListener('click', () => {
      let id = replyButton[i].getAttribute('id')
      let parentId = document.getElementById('appbundle_comment_parentId')
      let newComment = document.getElementById('new-comment')
      let comment = document.getElementById('comment-' + id)
      let cancelButton = document.getElementById('cancel-reply-' + id)
      let commentSection = comment.parentNode
      replyButton[i].classList.add('hidden')
      cancelButton.classList.remove('hidden')
      parentId.value = id
      commentSection.insertBefore(newComment, comment.nextSibling)
      newComment.classList.remove('mt-5')
      newComment.classList.add('mb-2')
      cancelButton.addEventListener('click', () => {
        replyButton[i].classList.remove('hidden')
        cancelButton.classList.add('hidden')
        newComment.classList.add('mt-5')
        newComment.classList.remove('mb-2')
        parentId.value = ''
        let comments = document.getElementById('comments')
        let commentSection = comments.parentNode
        commentSection.insertBefore(newComment, comments.nextSibling)
      }, false)
    }, false)
  }
}

let acceptCookie = document.getElementById('acceptcookies')
if (document.body.contains(acceptCookie)) {
  acceptCookie.addEventListener('click', () => {
    acceptCookies()
  }, false)
}

let copyImageLink = document.querySelector('#copy-link-image')
if (document.body.contains(copyImageLink)) {
  copyImageLink.addEventListener('click', () => {
    document.querySelector('#link-image').select()
    let copyAlert = document.getElementById('alert-copy-link-image')
    try {
      document.execCommand('copy')
      copyAlert.classList.remove('hidden')
      setTimeout( () => {
        copyAlert.classList.add('hidden')
      }, 2000)
    } catch (err) {
      console.log('Oops, unable to copy')
    }
  }, false)
}

let copyMarkdownImageEs = document.querySelector('#copy-markdown-image-es')
if (document.body.contains(copyMarkdownImageEs)) {
  copyMarkdownImageEs.addEventListener('click', () => {
    document.querySelector('#markdown-image-es').select()
    let copyAlert = document.getElementById('alert-copy-markdown-image-es')
    try {
      document.execCommand('copy')
      copyAlert.classList.remove('hidden')
      setTimeout( () => {
        copyAlert.classList.add('hidden')
      }, 2000)
    } catch (err) {
      console.log('Oops, unable to copy')
    }
  }, false)
}

let copyMarkdownImageEn = document.querySelector('#copy-markdown-image-en')
if (document.body.contains(copyMarkdownImageEn)) {
  copyMarkdownImageEn.addEventListener('click', () => {
    document.querySelector('#markdown-image-en').select()
    let copyAlert = document.getElementById('alert-copy-markdown-image-en')
    try {
      document.execCommand('copy')
      copyAlert.classList.remove('hidden')
      setTimeout( () => {
        copyAlert.classList.add('hidden')
      }, 2000)
    } catch (err) {
      console.log('Oops, unable to copy')
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

function checkCookies () {
  if (window.localStorage.acceptCookie !== 'true') {
    let cookies = document.getElementById('cookies')
    cookies.classList.remove('hidden')
  }
}

function acceptCookies () {
  window.localStorage.setItem('acceptCookie', 'true')
  let cookies = document.getElementById('cookies')
  cookies.classList.add('hidden')
}
