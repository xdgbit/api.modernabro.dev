import '../../css/app.css'
import 'altcha';
import axios from 'axios'
import Routes from '../config/routes.js'

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

const token = document.head.querySelector('meta[name="csrf-token"]')

if (token)
{
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
}

window.axios = axios
window.Routes = Routes

export {axios, Routes}