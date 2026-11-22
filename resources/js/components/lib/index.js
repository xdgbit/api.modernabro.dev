import AppInput from './form/AppInput.vue'
import AppSelect from './form/AppSelect.vue'
import AppToggle from './form/AppToggle.vue'
import AppPassword from './form/AppPassword.vue'
import AppUploadFile from './form/AppUploadFile.vue'
import AppDatePicker from './form/JalaliDatePicker.vue'
import AppInputNumeric from './form/AppInputNumeric.vue'

import Datatable from './Datatable.vue'
import BaseLoading from './BaseLoading.vue'

export default function registerGlobalComponents(app)
{
    app.component('BaseLoading', BaseLoading)
    app.component('Datatable', Datatable)
    app.component('AppInput', AppInput)
    app.component('AppSelect', AppSelect)
    app.component('AppToggle', AppToggle)
    app.component('AppPassword', AppPassword)
    app.component('AppDatePicker', AppDatePicker)
    app.component('AppUploadFile', AppUploadFile)
    app.component('AppInputNumeric', AppInputNumeric)
}