<template>
    <Flex 
        style="margin-bottom: 10px;"
        :justify="'space-between'"
        :gap="10"
        :wrap="'wrap'">
        <Flex 
            :gap="10"
            :align="'center'"
            :wrap="'wrap'">
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

            <Switch 
                checked-children="All" 
                un-checked-children="Only me"
                v-model:checked="showAllWorkers"/>

            <Segmented 
                v-model:value="selectedDay"
                :options="days"/>
        </FLex>

        <Flex 
            :gap="10"
            :align="'center'">
            <Button 
                v-if="! clock.data"
                :loading="clock.loading"
                @click="clockIn">
                ⏱️ Clock in
            </Button>

            <Button 
                v-if="clock.data"
                :loading="clock.loading"
                @click="clockOut">
                ⏱️ Clock out
            </Button>
        </FLex>

        <Flex 
            :gap="20"
            :align="'center'">
            <Flex 
                :vertical="true"
                :align="'center'">
                <span style="font-size: 12px; line-height: 1;">Total Hours</span>
                <span style="font-size: 20px; line-height: 1.3;">
                    {{ totalHours }} / {{ totalRealHours }}
                </span>
            </FLex>

            <Button 
                type="text"
                :loading="logout.loading"
                @click="userLogout">
                ➡️ 
            </Button>
        </FLex>
    </Flex>

    <Spin :spinning="loading">
        <div class="ec-container">
            <div id="ec">
            </div>
        </div>
    </Spin>

    <UnavailableDaysModal
        v-if="unavailableDaysModal.open"
        v-model:open="unavailableDaysModal.open"
        :worker="unavailableDaysModal.worker"
        :title="unavailableDaysModal.title"
        :start="unavailableDaysModal.start"
        :end="unavailableDaysModal.end"
        @save="getUnavailableDays"/>
</template>

<script>
import usersApi from '../../api/users'
import eventsApi from '../../api/events'
import { message, Flex, Button, Spin, Modal, Switch, Segmented, } from 'ant-design-vue'
import UnavailableDaysModal from '../Workers/UnavailableDaysModal.vue'
import { formatToYMDHIS, formatAMPM, } from '../../helpers/helpers'
import authApi from '../../api/auth'

export default {
    components: {
        Flex, Button, UnavailableDaysModal,
        Spin, Modal, Switch,
        Segmented,
    },
    data() {
        return {
            ec: null,
            workers: [],
            unavailableDaysModal: {
                open: false,
                worker: null,
                start: null,
                end: null,
                title: '',
            },
            loading: false,
            events: [],
            clock: {
                loading: false,
                data: null,
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
            week: [
                null,
                null,
            ],
            unavailableDays: [],
            logout: {
                loading: false,
            },
            showAllWorkers: true,
            selectedDay: null,
        }
    },
    computed: {
        resources() {
            return this.workers?.filter(worker => this.showAllWorkers || worker.id == authApi.user()?.id).map(worker => ({
                id: worker.id,
                title: {
                    html: `<div class="worker" data-id="${worker.id}" style="border-color: ${worker.color};">
                            <div class="worker-name">
                                ${worker.name}
                            </div>
                            <div class="worker-bottom">
                                <div>
                                    ${this.calcWorkerTime(worker)}h/${this.calcWorkerTime(worker, 'real')}h
                                </div>
                                ${authApi.user()?.id == worker.id ? '<div class="worker-settings">⚙️</div>' : ''}
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

            return Math.round(hours * 10) / 10
        },
        totalRealHours() {
            let hours = 0
            this.workers.map(worker => {
                hours += this.calcWorkerTime(worker, 'real')
            })

            return Math.round(hours * 10) / 10
        },
        isItPrevWeek() {
            if (! this.week[0]) {
                return false
            }

            const calendarEnd = this.week[1]

            const today = new Date()
            today.setHours(23, 59, 59, 999)

            return calendarEnd < today
        },
        calendarLabel() {
            if (! this.week[0]) {
                return false
            }

            const start = new Date(this.week[0])
            const end = new Date(this.week[1])
            end.setDate(end.getDate() - 1)

            const opts = { month: 'short', day: 'numeric' }

            return `${start.toLocaleDateString(undefined, opts)} – ${end.toLocaleDateString(undefined, opts)}`
        },
        days() {
            if (!this.week[0] || !this.week[1]) {
                return
            }

            const days = []
            const start = new Date(this.week[0])
            const end = new Date(this.week[1])

            end.setDate(end.getDate() - 1)

            for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                days.push({
                    label: d.toLocaleDateString('en-US', { weekday: 'short' }),
                    value: formatToYMDHIS(new Date(d), false),
                })
            }

            return days
        },
    },
    methods: {
        authApi,
        Modal,
        async getWorkers() {
            try {
                this.workers = await usersApi.getWorkers()
            } catch (err) {
                console.error(err)
            }
        },
        async getEvents(fetchInfo, successCallback) {
            try {
                this.loading = true
                const events = await eventsApi.get(
                    formatToYMDHIS(fetchInfo.start),
                    formatToYMDHIS(fetchInfo.end)
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
        toEcEvent(event) {  
            return {
                id: event.id,
                resourceIds: [event.user_id],
                start: new Date(event.start),
                end: event.end ? new Date(event.end) : new Date(),
                title: {html:`<div class="event-card"> - ${formatAMPM(event.end)}</div>`},
                color: event.user.color ?? 'black',
                extendedProps: {...event},
                display: event.type == 'real' ? 'background' : '',
            }
        }, 
        prev() {
            this.ec.prev()
            this.setCurrentWeek()
        },
        next() {
            this.ec.next()
            this.setCurrentWeek()
        },
        calcWorkerTime(worker, type = 'planed') {
            let time = 0

            this.events.filter(event => event.user_id == worker.id && event.type == type).map(event => {
                console.log(event.start, event.end ?? new Date(), this.diffInHours(event.start, event.end ?? new Date()))
                time += this.diffInHours(event.start, event.end ?? new Date())
            })

            return Math.round(time * 10) / 10
        },
        diffInHours(start, end) {
            const _start = new Date(start)
            const _end   = new Date(end)

            const diffMs = _end - _start
            const diffHours = diffMs / 1000 / 60 / 60

            return Math.round(diffHours * 10) / 10
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
        setCurrentWeek() {
            const view = this.ec.getView()
            const start = new Date(view.activeStart)
            const end = new Date(view.activeEnd)

            this.week = [start, end]
        },
        async getUnavailableDays() {
            try {
                this.unavailableDays = await usersApi.getAllUnavailableDays(
                    formatToYMDHIS(this.week[0], false),
                    formatToYMDHIS(this.week[1], false)
                )
            } catch (err) {
                console.error(err)
            }
        },
        setAllUnavailableDays() {
            if (! this.ec)  {
                return
            }

            this.ec.getEvents().forEach(event => {
                if (event.extendedProps?.unavailable) {
                    this.ec.removeEventById(event.id)
                }
            })

            this.unavailableDays.forEach(day => {
                this.ec.addEvent({
                    start: new Date(`${day.date} ${day.unavailable_from ? day.unavailable_from : '00:00:00'}`),
                    end: new Date(`${day.date} ${day.unavailable_to ? day.unavailable_to : '23:59:59'}`),
                    resourceIds: [day.user_id],
                    display: 'background',
                    color: '#b60d0da8',
                    extendedProps: {unavailable: true},
                })
            })
        },
        async getClockIn() {
            try {
                this.clock.loading = true
                this.clock.data = await usersApi.getClockIn(authApi.user()?.id)
            } catch (err) {
                console.error(err)
                message.error(err?.response?.data?.message ?? err.message)
            } finally {
                this.clock.loading = false
            }
        },
        async clockIn() {
            try {
                this.clock.loading = true
                this.clock.data = await usersApi.clockIn(authApi.user()?.id)
            } catch (err) {
                console.error(err)
                message.error(err?.response?.data?.message ?? err.message)
            } finally {
                this.clock.loading = false
            }
        },
        async clockOut() {
            try {
                this.clock.loading = true
                await usersApi.clockOut(authApi.user()?.id)
                this.clock.data = null
            } catch (err) {
                console.error(err)
                message.error(err?.response?.data?.message ?? err.message)
            } finally {
                this.clock.loading = false
            }
        },
        async userLogout() {
            try {
                this.logout.loading = true
                await authApi.logout()
                location.href = '/login'
            } catch (err) {
                console.error(err)
                message.error(err?.response?.data?.message ?? err.message)
            } finally {
                this.logout.loading = false
            }
        },
        scrollToDay(date) {
            this.$nextTick(() => {
                const scroller = document.querySelector('#ec .ec-body')
                if (! scroller) {
                    return
                }

                const days = scroller.querySelectorAll('.ec-day')
                if (! days.length) {
                    return
                }

                const firstDay = new Date(this.week[0])
                const targetDate = new Date(date)

                const dayDiff = Math.floor((targetDate - firstDay) / (1000 * 60 * 60 * 24))

                const index = Math.min(Math.max(dayDiff, 0), days.length - 1)

                const dayColumn = days[index]
                scroller.scrollLeft = dayColumn.offsetLeft
            })
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
        week: {
            handler() {
                this.setEventsForNotWorkingHours()
                this.selectedDay = formatToYMDHIS(this.week[0], false)
            },
            deep: true,
        },
        events: {
            handler() {
                this.getUnavailableDays()
            },
            deep: true,
        },
        unavailableDays: {
            handler() {
                this.setAllUnavailableDays()
            },
            deep: true,
        },
        selectedDay() {
            this.scrollToDay(this.selectedDay)  
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
            slotMinTime: '08:00:00',
            slotMaxTime: '22:00:00',
            slotHeight: 40,
            slotWidth: 30,
            selectable: true,
            slotEventOverlap: false,
            slotDuration: '00:15:00',
            firstDay: 1,
            eventSources: [{events: this.getEvents,}],
            selectable: false,
            eventStartEditable: false,
            eventDurationEditable: false,
        })

        this.setCurrentWeek()
        this.setEventsForNotWorkingHours()
        this.getWorkers()
        this.getClockIn()

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

#ec .ec-timeline .ec-time, .ec-timeline .ec-line {
    width: 30px;
}

#ec .ec-timeline .ec-time {
    overflow: visible;
}

#ec .ec-toolbar {
    display: none;
}

#ec .worker {
    border-left-width: 3px;
    padding-left: 5px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    width: 100%;
}

#ec .ec-resource span {
    width: 100%;
}

#ec .worker-bottom {
    display: flex;
    align-items: center;
    gap: 5px;
}

#ec .worker-settings {
    cursor: pointer;
}

#ec {
    height: calc(100vh - 88px); 
}

#ec .ec-container {
    overflow: auto; 
    flex-grow: 1;
}

#ec .worker-delete {
    cursor: pointer;
}

#ec .ec-events {
    border-right: 1px solid black;
}

#ec .event-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

#ec .ec-event-title {
    flex-grow: 1;
}

#ec .delete-event {
    cursor: pointer;
    padding-right: 5px;
}

#ec .delete-event:hover {
    scale: 1.1;
}
</style>