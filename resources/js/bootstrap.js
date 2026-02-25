import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import authApi from './api/auth'
import router from './routes/routes'

window.axios.interceptors.response.use(response => {
    return response
}, async err => {
    if (
        err.response &&
        err.response?.status == 419 ||
        (err.response?.status == 401 &&
            err.config.url != '/login')
    ) {
        authApi.logoutOnClient()

        router.push({ name: 'login' })

        return Promise.reject()
    }

    return Promise.reject(err)
})
