export default {
    async getWorkers() {
        const res = await axios.get('/api/users/workers')

        return res.data
    },
    async create(data) {
        const res = await axios.post('/api/users', data)

        return res.data
    },
    async delete(id) {
        const res = await axios.delete(`/api/users/${id}`)

        return res.data
    },
    async getUnavailableDays(id, start, end) {
        const query = new URLSearchParams({
            start,
            end,
        })
        
        const res = await axios.get(`/api/users/${id}/unavailable-days?${query}`)

        return res.data
    },
    async getAllUnavailableDays(start, end) {
        const query = new URLSearchParams({
            start,
            end,
        })
        
        const res = await axios.get(`/api/users/unavailable-days?${query}`)

        return res.data
    },
    async changeUnavailableDays(id, data) {       
        const res = await axios.post(`/api/users/${id}/unavailable-days`, data)

        return res.data
    },
    async getClockIn(id) {
        const res = await axios.get(`/api/users/${id}/clock-in`)

        return res.data
    },
    async clockIn(id) {
        const res = await axios.post(`/api/users/${id}/clock-in`)

        return res.data
    },
    async clockOut(id) {
        const res = await axios.post(`/api/users/${id}/clock-out`)

        return res.data
    },
}