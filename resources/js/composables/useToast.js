import { getCurrentInstance } from 'vue'

export function useToast() {

    const instance = getCurrentInstance()

    if (!instance) {
        throw new Error(
            'useToast() باید داخل setup() استفاده شود.'
        )
    }

    return instance.appContext.config.globalProperties.$toast
}