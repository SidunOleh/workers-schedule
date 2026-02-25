export default {
    async login(credentials) {
        await axios.get('/sanctum/csrf-cookie')
        
        const res = await axios.post('/login', credentials)

        const data = res.data

        localStorage.setItem('auth', JSON.stringify({
            user: data,
        }))
    },
    async logout() {
        await axios.post('/logout')

        this.logoutOnClient()
    },
    logoutOnClient() {
        localStorage.removeItem('auth')
    },
    isLogged() {
        const auth = JSON.parse(localStorage.getItem('auth'))

        return Boolean(auth)
    },
    user() {
        const auth = JSON.parse(localStorage.getItem('auth'))

        return auth?.user
    },
}