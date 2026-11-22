import './bootstrap'

import '../../css/grid.css'
import '../../css/datepicker.css'
import '@fortawesome/fontawesome-free/css/all.min.css'

import {createApp} from 'vue'
import {createPinia} from 'pinia'

import App from './../App.vue'
import router from './../router'
import GlobalComponents from './../components/lib'

import {Toaster} from 'vue-sonner'
import 'vue-sonner/style.css'

import toastPlugin from '@/plugins/toast'

import {jsPanel} from 'jspanel4'
import 'jspanel4/dist/jspanel.min.css'

import JsPanelV4, {
    setJsPanelVueApp,
} from './../services/jsPanel'

import {createVfm} from 'vue-final-modal'
import 'vue-final-modal/style.css'

import {
    ModuleRegistry,
    AllCommunityModule,
    InfiniteRowModelModule,
} from 'ag-grid-community'

import {initBeams} from './../services/beams'

// AG Grid
ModuleRegistry.registerModules([
    AllCommunityModule,
    InfiniteRowModelModule,
])

// jsPanel
window.jsPanel = jsPanel
window.JsPanelV4 = JsPanelV4

// Create Vue application
const app = createApp(App)
const vfm = createVfm()

app.use(createPinia())
app.use(router)
app.component('Toaster', Toaster)
app.use(toastPlugin)
app.use(GlobalComponents)
app.use(vfm)

setJsPanelVueApp(app)

// Mount Vue
app.mount('#app')

// Initialize Pusher Beams
async function initializeNotifications()
{
    const interests = [
        window.private_pusher_channel,
        window.expert_common_channel,
    ]

    // Register super-admin interest only for super admins
    if (window.authUserExpert?.is_super_admin === true)
    {
        interests.push(window.super_admin_common_channel)
    }

    // Remove empty and duplicate interests
    const uniqueInterests = [
        ...new Set(interests.filter(
            interest => typeof interest === 'string' && interest.length > 0
        )),
    ]

    await initBeams(uniqueInterests)

    console.log('Pusher Beams initialized')
}

initializeNotifications().catch((error) =>
{
    console.error('Pusher Beams initialization failed:', error)
})
