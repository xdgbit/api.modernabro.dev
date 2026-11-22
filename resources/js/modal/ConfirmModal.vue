<template>
    <VueFinalModal
        class="modal-wrapper"
        content-class="modal-content"
        :esc-to-close="false"
        :click-to-close="false"
    >
        <div class="modal-box">

            <div class="modal-header">
                <div class="modal-icon">
                    ?
                </div>

                <div class="modal-title">
                    <h3>{{ title }}</h3>
                </div>
            </div>

            <div class="modal-body">
                {{ message }}
            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-cancel"
                    @click="$emit('cancel')"
                >
                    {{ cancelText }}
                </button>

                <button
                    type="button"
                    class="btn btn-confirm"
                    @click="$emit('confirm')"
                >
                    {{ confirmText }}
                </button>

            </div>

        </div>
    </VueFinalModal>
</template>

<script>
import { VueFinalModal } from 'vue-final-modal'

export default {
    name: 'ConfirmModal',

    components: {
        VueFinalModal,
    },

    props: {
        title: {
            type: String,
            default: 'تأیید عملیات',
        },

        message: {
            type: String,
            default: '',
        },

        confirmText: {
            type: String,
            default: 'تأیید',
        },

        cancelText: {
            type: String,
            default: 'انصراف',
        },
    },
}
</script>

<style scoped>

.modal-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}

.modal-content {
    width: 100%;
    max-width: 400px;
}

.modal-box {
    width: 100%;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    direction: rtl;

    box-shadow:
        0 20px 40px rgba(0, 0, 0, .18),
        0 5px 15px rgba(0, 0, 0, .08);
}

/* Header */

.modal-header {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 16px 18px;

    border-bottom: 1px solid #eef0f3;
}

.modal-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 36px;
    height: 36px;

    flex-shrink: 0;

    border-radius: 50%;

    background: #fff7ed;
    color: #f97316;

    font-size: 16px;
    font-weight: 700;
}

.modal-title {
    flex: 1;
}

.modal-title h3 {
    margin: 0;

    color: #111827;

    font-size: 15px;
    font-weight: 700;
}

/* Body */

.modal-body {
    padding: 18px;

    color: #4b5563;

    font-size: 13px;
    line-height: 1.8;
}

/* Footer */

.modal-footer {
    display: flex;
    align-items: center;
    gap: 8px;

    padding: 12px 18px;

    background: #f8fafc;

    border-top: 1px solid #eef0f3;
}

/* Buttons */

.btn {
    height: 36px;

    padding: 0 16px;

    border-radius: 7px;

    font-family: inherit;
    font-size: 12px;
    font-weight: 600;

    cursor: pointer;

    transition: .15s ease;
}

.btn-cancel {
    background: #fff;
    color: #374151;

    border: 1px solid #d1d5db;
}

.btn-cancel:hover {
    background: #f3f4f6;
}

.btn-confirm {
    background: #2563eb;
    color: #fff;

    border: 1px solid #2563eb;
}

.btn-confirm:hover {
    background: #1d4ed8;
}

</style>