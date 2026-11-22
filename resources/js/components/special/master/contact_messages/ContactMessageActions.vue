<script setup>
import axios from 'axios'
import Swal from 'sweetalert2'
import {toast as $toast} from 'vue-sonner'
import {Eye, Trash2} from 'lucide-vue-next'
import ContactMessagePanel from './ContactMessagePanel.vue'

const props = defineProps({
    params: {type: Object, required: true},
})

const messageId = props.params.data?.id

function refresh()
{
    props.params.api?.purgeInfiniteCache()
}

function viewMessage()
{
    if (!messageId) return

    const panelName = `jsPanelVariableContactMessage${messageId}`

    window.JsPanelV4({
        component: ContactMessagePanel,
        modal: false,
        reload: true,
        variableName: panelName,
        theme: 'info',
        position: 'center',
        z_index: 10000000,
        preFooterText: 'در حال بارگذاری ...',
        headerTitle: 'مشاهده پیام',
        ajaxData: {},
        props: {
            messageId,
            jsPanelVariableName: panelName,
            onSaved: () => refresh(),
        },
        onclose_function: false,
        just_one_panel: true,
        container: 'window',
    })
}

async function remove()
{
    if (!messageId) return

    const result = await Swal.fire({
        title: 'حذف پیام؟',
        text: 'آیا از حذف این پیام مطمئن هستید؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'لغو',
        confirmButtonColor: '#d33',
        reverseButtons: true,
    })

    if (!result.isConfirmed) return

    try
    {
        await axios.delete(Routes.contact_message.delete(messageId))

        $toast.success('پیام با موفقیت حذف شد.')

        refresh()
    }
    catch (error)
    {
        console.error('خطا در حذف پیام:', error)

        $toast.error(
            error.response?.data?.message ??
            'حذف پیام با خطا مواجه شد.'
        )
    }
}
</script>

<template>
    <div class="flex items-center justify-center whitespace-nowrap mt-2">
        <button
            type="button"
            title="مشاهده"
            @click="viewMessage"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-blue-50 hover:text-blue-600"
        >
            <Eye :size="20"/>
        </button>

        <button
            type="button"
            title="حذف"
            @click="remove"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600"
        >
            <Trash2 :size="20"/>
        </button>
    </div>
</template>