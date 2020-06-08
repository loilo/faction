import { key } from './util'

export default function applyFocusRing() {
  // Apply "focus-ring" class to elements focused immediately after a keypress
  window.addEventListener('keydown', key.markPressed)
  window.addEventListener('focus', key.markPressed)

  window.addEventListener('focusin', event => {
    if (key.isPressed) {
      event.target.classList.add('focus-ring')
    }
  })

  window.addEventListener('focusout', event =>
    event.target.classList.remove('focus-ring'),
  )
}
