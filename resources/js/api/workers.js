export default {
    async getAll() {
        const res = await axios.get('/api/workers')

        return res.data
    },
    async create(data) {
        const res = await axios.post('/api/workers', data)

        return res.data
    },
    async delete(id) {
        const res = await axios.delete(`/api/workers/${id}`)

        return res.data
    },
    async getUnavailableDays(workerId, start, end) {
        const query = new URLSearchParams({
            start,
            end,
        })
        
        const res = await axios.get(`/api/workers/${workerId}/unavailable-days?${query}`)

        return res.data
    },
    async changeUnavailableDays(workerId, data) {       
        const res = await axios.post(`/api/workers/${workerId}/unavailable-days`, data)

        return res.data
    },
}