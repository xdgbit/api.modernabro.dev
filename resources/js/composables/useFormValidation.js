import { ref } from 'vue'

import {
    useFormErrors,
} from '@/composables/useFormErrors'


export const useFormValidation = () =>
{

    const {
        setValidationError,
        setApiErrors,
        clearAll,
        clearField,
        getErrors,
    } = useFormErrors()


    const validationStarted = ref(false)


    const getError = (field) =>
    {
        return getErrors(field)[0] ?? ''
    }


    const validateRequired = (
        field,
        value,
        message
    ) =>
    {

        const normalized =
            value?.trim?.() ?? value ?? ''


        if (!normalized)
        {

            setValidationError(
                field,
                message
            )

            return false
        }


        clearField(field)

        return true
    }


    const onFieldInput = (
        field,
        value,
        message
    ) =>
    {

        if (!validationStarted.value)
        {
            return
        }


        const normalized =
            value?.trim?.() ?? value ?? ''


        if (!normalized)
        {

            setValidationError(
                field,
                message
            )

            return
        }


        clearField(field)

    }


    const startValidation = () =>
    {
        validationStarted.value = true
    }


    const resetValidation = () =>
    {

        validationStarted.value = false

        clearAll()

    }


    return {

        validationStarted,

        getError,

        validateRequired,

        onFieldInput,

        startValidation,

        resetValidation,

        setApiErrors,

        clearAll,

        clearField,

    }

}