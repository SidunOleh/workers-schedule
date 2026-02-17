export default {
    async get(start, end) {
        const res = await axios.get(`/api/events?start=${start}&end=${end}`)

        return res.data
    },
    async create(data) {
        const res = await axios.post('/api/events', data)

        return res.data
    },
    async edit(id, data) {
        const res = await axios.put(`/api/events/${id}`, data)

        return res.data
    },
    async delete(id) {
        const res = await axios.delete(`/api/events/${id}`)

        return res.data
    },
    async clear(start, end) {
        const res = await axios.post(`/api/events/clear?start=${start}&end=${end}`)

        return res.data
    },
    async copy(start, end) {
        const res = await axios.post(`/api/events/copy?start=${start}&end=${end}`)

        return res.data
    },
}