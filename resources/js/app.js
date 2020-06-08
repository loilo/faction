import Turbolinks from 'turbolinks'
import { Application } from 'stimulus'
import { definitionsFromContext } from 'stimulus/webpack-helpers'
import applyFocusRing from './helpers/focus-ring'

// Initialize Turbolinks
Turbolinks.start()

// Initialize Stimulus
const application = Application.start()
const context = require.context('./controllers', true, /\.js$/)
application.load(definitionsFromContext(context))

// Apply `focus-ring` class where appropriate
applyFocusRing()
