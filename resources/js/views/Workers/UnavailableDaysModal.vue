<template>
    <Modal 
        :title="title"
        :open="open"
        :footer="null"
        @cancel="$emit('update:open', false)">

        <Segmented 
            v-model:value="selectedDay"
            :options="days"/>

        <Form 
            v-if="selectedDay"
            layout="vertical"
            style="margin-top: 10px;">
            <FormItem label="Unavailable">
                <Switch v-model:checked="selectedDate.unavailable"/>
            </FormItem>

            <FormItem label="Unavailable from">
                <TimePicker 
                    v-model:value="selectedDate.unavailable_from"
                    valueFormat="HH:mm"
                    format="HH:mm"/>
            </FormItem>

            <FormItem label="Unavailable to">
                <TimePicker 
                    v-model:value="selectedDate.unavailable_to"
                    valueFormat="HH:mm"
                    format="HH:mm"/>
            </FormItem>

            <Button 
                :loading="loading"
                @click="save">
                Save
            </Button>
        </Form>
    </Modal>
</template>

<script>
import { Modal, message, Segmented, Switch, FormItem, Form, TimePicker, Button, } from 'ant-design-vue'
import workersApi from '../../api/workers'

export default {
    props: [
        'title',
        'open',
        'worker',
        'start',
        'end',
    ],
    components: {
        Modal, Segmented, Switch,
        FormItem, Form, TimePicker,
        Button,
    },
    data() {
        return {
            data: [],
            selectedDay: null,
            errors: {},
            loading: false,
            errors: {},
        }
    },    
    computed: {
        days() {
            return this.data.map(item => ({
                label: this.getDayName(item.date),
                value: item.date,
                className: item.unavailable ? 'unavailable' : ''
            }))
        },
        selectedDate() {
            return this.data.find(day => day.date == this.selectedDay)
        },
    },
    methods: {
        async getUnavailableDays() {
            try {
                this.loading = true

                const data = await workersApi.getUnavailableDays(
                    this.worker, 
                    this.formatD(this.start), 
                    this.formatD(this.end)
                )

                data.forEach(item => {
                    this.data.forEach(_item => {
                        if (_item.date == item.date) {
                            _item.unavailable = true
                            _item.unavailable_from = item.unavailable_from
                            _item.unavailable_to = item.unavailable_to
                        }
                    })
                })
            } catch (err) {
                console.error(err)
            } finally {
                this.loading = false
            }
        },
        formatD(date) {
            return `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
        },
        getDayName(date) {
            return new Date(date).toLocaleDateString('en-US', { weekday: 'short' })
        },
        async save() {
            try {
                this.loading = true
                await workersApi.changeUnavailableDays(this.worker, {days: this.data})
                message.success('Successfully saved.')
            } catch (err) {
                 message.error(err?.response?.data?.message ?? err.message)
            } finally {
                this.loading = false
            }
        },
    },
    mounted() {
        for (let d = new Date(this.start); d <= this.end; d.setDate(d.getDate() + 1)) {
            this.data.push({
                date: this.formatD(d),
                unavailable: false,
                unavailable_from: null,
                unavailable_to: null,
            })
        }

        this.selectedDay = this.data[0].date

        this.getUnavailableDays()
    },
}
</script>