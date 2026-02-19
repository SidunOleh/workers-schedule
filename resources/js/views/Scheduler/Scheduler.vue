<template>
    <Flex 
        style="margin-bottom: 10px;"
        :justify="'space-between'"
        :gap="10">
        <Flex 
            :gap="10"
            :align="'center'">
            <Flex 
                :gap="5"
                :align="'center'">
                <Button @click="prev">
                    Prev
                </Button>
                <Button @click="next">
                    Next
                </Button>
            </FLex>

            <strong>{{ calendarLabel }}</strong>
        </FLex>

        <Flex 
            :gap="10"
            :align="'center'">
            <Button @click="createWorker">
                + 👷 Worker
            </Button>
            <Button 
                :loading="clear.loading"
                @click="clearEventsConfirm">
                🆑 Clear
            </Button>
             <Button 
                :loading="copy.loading"
                @click="copyEvents">
                📋 Copy
            </Button>
        </FLex>

        <Flex 
            :gap="5"
            :align="'center'">
            <Flex 
                :vertical="true"
                :align="'center'">
                <span style="font-size: 12px; line-height: 1;">Total Hours</span>
                <span style="font-size: 20px; line-height: 1.1;">{{ totalHours }}</span>
            </FLex>
        </FLex>
    </Flex>

    <Spin :spinning="loading">
        <div class="ec-container">
            <div id="ec">
            </div>
        </div>
    </Spin>

    <WorkersModal
        v-if="workersModal.open"
        v-model:open="workersModal.open"
        :action="workersModal.action"
        :worker="workersModal.worker"
        :title="workersModal.title"
        @create="data => workers.push(data)"/>

    <UnavailableDaysModal
        v-if="unavailableDaysModal.open"
        v-model:open="unavailableDaysModal.open"
        :worker="unavailableDaysModal.worker"
        :title="unavailableDaysModal.title"
        :start="unavailableDaysModal.start"
        :end="unavailableDaysModal.end"/>
</template>

<script>
import workersApi from '../../api/workers'
import eventsApi from '../../api/events'
import { message, Flex, Button, Spin, Modal, } from 'ant-design-vue'
import WorkersModal from '../Workers/Modal.vue'
import UnavailableDaysModal from '../Workers/UnavailableDaysModal.vue'

export default {
    components: {
        Flex, Button, WorkersModal,
        Spin, Modal, UnavailableDaysModal,
    },
    data() {
        return {
            workers: [],
            workersModal: {
                open: false,
                action: 'create',
                worker: null,
                title: '',
            },
            unavailableDaysModal: {
                open: false,
                worker: null,
                start: null,
                end: null,
                title: '',
            },
            loading: false,
            events: [],
            calendarLabel: null,
            clear: {
                loading: false,
            },
            copy: {
                loading: false,
            },
            schedule: {
                1: {start: 14, end: 20},
                2: {start: 9,  end: 15},
                3: {start: 14, end: 20},
                4: {start: 9,  end: 21},
                5: {start: 9,  end: 21},
                6: {start: 9,  end: 21},
                0: {start: 9,  end: 21},
            },
        }
    },
    computed: {
        resources() {
            return this.workers?.map(worker => ({
                id: worker.id,
                title: {
                    html: `<div class="worker" data-id="${worker.id}" style="border-color: ${worker.color};">
                            <div class="worker-name">
                                ${worker.name}
                            </div>
                            <div class="worker-bottom">
                                <div>
                                    ${this.calcWorkerTime(worker)}h/week
                                </div>
                                <div class="worker-settings">
                                    ⚙️
                                </div>
                                <div class="worker-delete">
                                    🗑
                                </div>
                            </div>
                        </div>
                        `,
                }
            })) ?? []
        },
        totalHours() {
            let hours = 0
            this.workers.map(worker => {
                hours += this.calcWorkerTime(worker)
            })

            return hours
        },
    },
    methods: {
        Modal,
        async getWorkers() {
            try {
                this.workers = await workersApi.getAll()
            } catch (err) {
                console.error(err)
            }
        },
        createWorker() {
            this.workersModal.action = 'create'
            this.workersModal.worker = null
            this.workersModal.open = true
            this.workersModal.title = 'Create worker'
        },
        async getEvents(fetchInfo, successCallback) {
            try {
                this.loading = true
                const events = await eventsApi.get(
                    this.formatD(fetchInfo.start),
                    this.formatD(fetchInfo.end)
                )
                this.events = events
                successCallback(events.map(
                    event => this.toEcEvent(event)
                ))
            } catch (err) {
                console.error(err?.response?.data?.message ?? err.message)
            } finally {
                this.loading = false
            }
        },
        formatD(date, withTime = true) {
            let dateStr = `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
            
            if (withTime) {
                dateStr += ` ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}:${String(date.getSeconds()).padStart(2, '0')}`
            }

            return dateStr
        },
        toEcEvent(event) {  
            return {
                id: event.id,
                resourceIds: [event.worker_id],
                start: new Date(event.start),
                end: new Date(event.end),
                title: {html:`${event.worker.name}`},
                color: event.worker.color,
                extendedProps: {...event},
            }
        },
        async createEvent(e) {
            try {
                const data = await eventsApi.create({
                    worker_id: e.resource.id,
                    start: this.formatD(e.start, true),
                    end: this.formatD(e.end, true),
                })

                this.events.push(data)
                this.ec.addEvent(this.toEcEvent(data))
            } catch (err) {
                message.error(err?.response?.data?.message ?? err.message)
                this.ec.unselect()
            }
        },  
        async editEvent(e) {
            try {
                const data = await eventsApi.edit(e.extendedProps.id, {
                    worker_id: e.resourceIds[0],
                    start: this.formatD(e.start, true),
                    end: this.formatD(e.end, true),
                })

                this.events = this.events.map(event => {
                    if (event.id == e.extendedProps.id) {
                        return data
                    }

                    return event
                })
                this.ec.updateEvent(this.toEcEvent(data))
            } catch (err) {
                message.error(err?.response?.data?.message ?? err.message)
                e.revert()
            }
        },
        async deleteEvent(id) {
            try {
                await eventsApi.delete(id)

                this.events = this.events.filter(event => event.id != id)
                this.ec.removeEventById(id)
            } catch (err) {
                message.error(err?.response?.data?.message ?? err.message)
            }
        },
        prev() {
            this.ec.prev()
            this.calendarLabel = this.formatCalendarDate()
        },
        next() {
            this.ec.next()
            this.calendarLabel = this.formatCalendarDate()
        },
        calcWorkerTime(worker) {
            let time = 0

            this.events.filter(event => event.worker_id == worker.id).map(event => {
                time += this.diffInHours(event.start, event.end)
            })

            return time
        },
        diffInHours(start, end) {
            const _start = new Date(start)
            const _end   = new Date(end)

            const diffMs = _end - _start
            const diffHours = diffMs / 1000 / 60 / 60

            return diffHours
        },
        formatCalendarDate() {
            const view = this.ec.getView()

            const start = new Date(view.activeStart)
            const end = new Date(view.activeEnd)
            end.setDate(end.getDate() - 1)

            const opts = { month: 'short', day: 'numeric' }

            return `${start.toLocaleDateString(undefined, opts)} – ${end.toLocaleDateString(undefined, opts)}`
        },
        async deleteWorker(id) {
            try {
                await workersApi.delete(id)

                this.workers = this.workers.filter(worker => worker.id != id)
            } catch (err) {
                console.error(err)

                message.error(err?.response?.data?.message ?? err.message)
            }
        },
        clearEventsConfirm() {
            Modal.confirm({
                title: 'Are you sure you want to clear?',
                okText: 'Yes',
                cancelText: 'No',
                onOk: this.clearEvents,
            })
        },
        async clearEvents() {
            try {
                this.clear.loading = true

                const view = this.ec.getView()
                const start = new Date(view.activeStart)
                const end = new Date(view.activeEnd)
                end.setDate(end.getDate() - 1)

                await eventsApi.clear(this.formatD(start), this.formatD(end))

                message.success('Successfully cleared.')

                this.ec.refetchEvents()
            } catch (err) {
                console.error(err)

                message.error(err?.response?.data?.message ?? err.message)
            } finally {
                this.clear.loading = false
            }
        },
        async copyEvents() {
            try {
                this.copy.loading = true

                const view = this.ec.getView()
                const start = new Date(view.activeStart)
                const end = new Date(view.activeEnd)
                end.setDate(end.getDate() - 1)

                await eventsApi.copy(this.formatD(start), this.formatD(end))

                message.success('Successfully copied.')

                this.ec.refetchEvents()
            } catch (err) {
                console.error(err)

                message.error(err?.response?.data?.message ?? err.message)
            } finally {
                this.copy.loading = false
            }
        },
        setEventsForNotWorkingHours() {
            if (! this.ec)  {
                return
            }

            this.ec.getEvents().forEach(event => {
                if (event.extendedProps?.isNotWorking) {
                    this.ec.removeEventById(event.id)
                }
            })

            const view = this.ec.getView()
            const start = new Date(view.activeStart)
            const end = new Date(view.activeEnd)

            let current = new Date(start)

            while (current < end) {
                const day = current.getDay()
                const working = this.schedule[day]

                this.workers.forEach(worker => {
                    if (working.start > 0) {
                        this.ec.addEvent({
                            start: new Date(current.getFullYear(), current.getMonth(), current.getDate(), 0, 0),
                            end: new Date(current.getFullYear(), current.getMonth(), current.getDate(), working.start, 0),
                            resourceIds: [worker.id],
                            display: 'background',
                            color: '#0000005e',
                            extendedProps: {isNotWorking: true},
                        })
                    }

                    if (working.end < 24) {
                        this.ec.addEvent({
                            start: new Date(current.getFullYear(), current.getMonth(), current.getDate(), working.end, 0),
                            end: new Date(current.getFullYear(), current.getMonth(), current.getDate(), 23, 59),
                            resourceIds: [worker.id],
                            display: 'background',
                            color: '#0000005e',
                            extendedProps: {isNotWorking: true},
                        })
                    }

                })

                current.setDate(current.getDate() + 1)
            }
        },
        isOutsideSchedule(start, end) {
            const day = new Date(start).getDay()
            const working = this.schedule[day]

            const startHour = new Date(start).getHours()
            const endHour = new Date(end).getHours()

            if (startHour < working.start || endHour > working.end) {
                return true
            }

            return false
        },
        isValidDateRange(start, end) {
            const startDate = new Date(start).getDate()
            const endDate = new Date(end).getDate()


            if (startDate != endDate) {
                return false
            }

            return true
        },
    },
    watch: {
        resources: {
            handler() { 
                this.ec.setOption('resources', this.resources)

                this.setEventsForNotWorkingHours()
            },
            deep: true,
        },
        events: {
            handler() {
                this.ec.setOption('resources', this.resources)

                this.setEventsForNotWorkingHours()
            },
            deep: true,
        },
    },
    mounted() {
        this.ec = EventCalendar.create(document.getElementById('ec'), {
            view: 'resourceTimelineWeek',
            headerToolbar: {
                start: '',
                center: '',
                end: '',
            },
            slotMinTime: '09:00:00',
            slotMaxTime: '21:00:00',
            slotHeight: 40,
            selectable: true,
            slotEventOverlap: false,
            slotDuration: '01:00:00',
            select: e => {
                if (this.isOutsideSchedule(e.start, e.end)) {
                    message.error('Event is outside schedule!')
                    this.ec.unselect()
                    return
                }

                if (!this.isValidDateRange(e.start, e.end)) {
                    message.error('Invalid date range!')
                    this.ec.unselect()
                    return
                }

                this.createEvent(e)
            },
            eventDrop: e => {
                if (this.isOutsideSchedule(e.event.start, e.event.end)) {
                    message.error('Event is outside schedule!')
                    e.revert()
                    return
                }

                if (!this.isValidDateRange(e.event.start, e.event.end)) {
                    message.error('Invalid date range!')
                    e.revert()
                    return
                }

                this.editEvent(e.event)
            },
            eventResize: e => {
                if (this.isOutsideSchedule(e.event.start, e.event.end)) {
                    message.error('Event is outside schedule!')
                    e.revert()
                    return
                }

                if (!this.isValidDateRange(e.event.start, e.event.end)) {
                    message.error('Invalid date range!')
                    e.revert()
                    return
                }

                this.editEvent(e.event)
            },
            eventClick: e => {
                Modal.confirm({
                    title: 'Are you sure you want to delete?',
                    okText: 'Yes',
                    cancelText: 'No',
                    onOk: () => this.deleteEvent(e.event.id),
                })
            },
            eventSources: [{events: this.getEvents,}]
        })

        this.calendarLabel = this.formatCalendarDate()

        this.getWorkers()

        document.addEventListener('click', e => {
            if (! e.target.classList.contains('worker-delete')) {
                return
            }

            const el = e.target.closest('.worker')

            Modal.confirm({
                title: 'Are you sure you want to delete?',
                okText: 'Yes',
                cancelText: 'No',
                onOk: () => this.deleteWorker(el.getAttribute('data-id')),
            })
        })

        document.addEventListener('click', e => {
            if (! e.target.classList.contains('worker-settings')) {
                return
            }

            const el = e.target.closest('.worker')

            this.unavailableDaysModal.worker = el.getAttribute('data-id')
            this.unavailableDaysModal.open = true

            const view = this.ec.getView()
            const start = new Date(view.activeStart)
            const end = new Date(view.activeEnd)
            end.setDate(end.getDate() - 1)
            this.unavailableDaysModal.start = start
            this.unavailableDaysModal.end = end
            this.unavailableDaysModal.title = `Settings ${this.formatCalendarDate()}`
        })

        this.$nextTick(() => {
            const scroller = document.querySelector('#ec .ec-body') || document.getElementById('ec')

            scroller.addEventListener('wheel', e => {
                e.preventDefault()
                scroller.scrollLeft += e.deltaY
            }, { passive: false })
        })
    },
}
</script>

<style>
#ec .ec-timeline .ec-body .ec-days {
    flex-basis: 40px !important;
}

#ec  .ec-timeline .ec-sidebar .ec-resource {
    flex-basis: 40px !important;
}

#ec .ec-toolbar {
    display: none;
}

.worker {
    border-left-width: 3px;
    padding-left: 5px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    width: 100%;
}

.ec-resource span {
    width: 100%;
}

.worker-name {
}

.worker-bottom {
    display: flex;
    align-items: center;
    gap: 5px;
}

.worker-settings {
    cursor: pointer;
}

#ec {
    height: calc(100vh - 85px); 
}

.ec-container {
    overflow: auto; 
    flex-grow: 1;
}

.worker-delete {
    cursor: pointer;
}

#ec .ec-events {
    border-right: 1px solid black;
}
</style>