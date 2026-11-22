import { toast } from 'vue-sonner'
// import 'vue-sonner/style.css'

const $toast = {

    success(message, options = {}) {
        return toast.success(message, options)
    },

    error(message, options = {}) {
        return toast.error(message, options)
    },

    warning(message, options = {}) {
        return toast.warning(message, options)
    },

    info(message, options = {}) {
        return toast.info(message, options)
    },

    loading(message, options = {}) {
        return toast.loading(message, options)
    },

    promise(promise, options = {}) {
        return toast.promise(promise, options)
    },

    dismiss(id) {
        return toast.dismiss(id)
    },

    custom(component, options = {}) {
        return toast.custom(component, options)
    },

}

export default {

    install(app) {
        app.config.globalProperties.$toast = $toast
        globalThis.$toast = $toast
    },

}