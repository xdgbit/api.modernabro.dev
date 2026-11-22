<template>
    <ModalsContainer />
</template>

<script setup>
import {
    ModalsContainer,
    useModal,
} from 'vue-final-modal'

import ConfirmModal from './ConfirmModal.vue'
import { setModalApi } from './manager'

const confirm = (options = {}) => {

    return new Promise((resolve) => {

        const {
            open,
            close,
            destroy,
        } = useModal({

            component: ConfirmModal,

            attrs: {

                title: options.title,
                message: options.message,

                confirmText:
                    options.confirmText || 'تأیید',

                cancelText:
                    options.cancelText || 'انصراف',

                onConfirm: async () => {

                    resolve(true)

                    await close()
                    destroy()
                },

                onCancel: async () => {

                    resolve(false)

                    await close()
                    destroy()
                },
            },
        })

        open()
    })
}

setModalApi({
    confirm,
})
</script>