let modalApi = null

export function setModalApi(api) {
    modalApi = api
}

export function getModalApi() {
    if (!modalApi) {
        throw new Error('Modal system has not been initialized.')
    }

    return modalApi
}

export const modal = {
    async confirm(options = {}) {
        return getModalApi().confirm(options)
    },
}