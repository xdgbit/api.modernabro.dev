import { reactive } from 'vue'
import { useErrorStore } from '@/stores/error'

export function useFormErrors() {

    const errorStore = useErrorStore()

    const validationErrors = reactive({})


    const hasError = (field) => {

        const validationError =
            validationErrors[field]

        const apiError =
            errorStore.apiErrors[field]

        return Boolean(
            validationError?.length ||
            apiError?.length
        )
    }


    const getErrors = (field) => {

        const validationError =
            validationErrors[field]

        if (validationError?.length) {
            return validationError
        }


        const apiError =
            errorStore.apiErrors[field]

        if (!apiError) {
            return []
        }


        return Array.isArray(apiError)
            ? apiError
            : [apiError]
    }


    const setValidationError = (field, message) => {

        validationErrors[field] =
            Array.isArray(message)
                ? message
                : [message]
    }


    const setApiErrors = (errors) => {

        errorStore.set(errors)
    }


    const clearValidationError = (field) => {

        delete validationErrors[field]
    }


    const clearField = (field) => {

        delete validationErrors[field]

        errorStore.clear(field)
    }


    const clearAll = () => {

        Object.keys(validationErrors).forEach(
            field => {
                delete validationErrors[field]
            }
        )

        errorStore.clearAll()
    }


    return {
        validationErrors,
        hasError,
        getErrors,
        setValidationError,
        setApiErrors,
        clearValidationError,
        clearField,
        clearAll,
    }
}