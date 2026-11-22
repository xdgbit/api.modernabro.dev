import { defineStore } from 'pinia'

export const useErrorStore = defineStore('error', {
    state: () => ({
        apiErrors: {},
    }),

    actions: {

        set(errors) {
            this.apiErrors = errors ?? {}
        },

        setField(field, errors) {
            this.apiErrors[field] = Array.isArray(errors)
                ? errors
                : [errors]
        },

        clear(field) {
            delete this.apiErrors[field]
        },

        clearAll() {
            this.apiErrors = {}
        },
    },
})