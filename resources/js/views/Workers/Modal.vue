<template>
    <Modal 
        :title="title"
        :open="open"
        :footer="null"
        @cancel="$emit('update:open', false)">

        <Form layout="vertical">

            <FormItem
                label="Name"
                :required="true"
                has-feedback
                :validate-status="errors['name'] ? 'error' : ''"
                :help="errors.name">
                <Input
                    placeholder="Enter name"
                    v-model:value="data.name"/>
            </FormItem>

            <FormItem
                label="Color"
                :required="true"
                has-feedback
                :validate-status="errors['color'] ? 'error' : ''"
                :help="errors.color">
                <CompactPicker v-model="data.color"/>
            </FormItem>

            <Button
                :loading="loading"
                @click="action == 'create' ? create() : edit()">
                Save
            </Button>

        </Form>

    </Modal>
</template>

<script>
import { Modal, Button, Form, FormItem, Input, message, } from 'ant-design-vue'
import workersApi from '../../api/workers'
import 'vue-color/style.css'
import { CompactPicker } from 'vue-color'

export default {
    props: [
        'title',
        'open',
        'action',
        'worker',
    ],
    components: {
        Modal, Button, Form, 
        FormItem, Input, CompactPicker,
    },
    data() {
        return {
            data: {
                name: '',
                color: '#4d4d4d',
            },
            errors: {},
            loading: false,
        }
    },    
    methods: {
        async create() {
            try {
                this.loading = true
                this.errors = {}
                const data = await workersApi.create(this.data)
                message.success('Successfully created.')
                this.$emit('create', data)
                this.$emit('update:open', false)
            } catch (err) {
                if (err?.response?.status == 422) {
                    this.errors = err?.response?.data?.errors
                } else {
                    message.error(err?.response?.data?.message ?? err.message)
                }
            } finally {
                this.loading = false
            }
        },
        async edit() {
            try {
                this.loading = true
                this.errors = {}
                const data = await workersApi.edit(this.data.id, this.data)
                message.success('Successfully saved.')
                this.$emit('edit', data)
            } catch (err) {
                if (err?.response?.status == 422) {
                    this.errors = err?.response?.data?.errors
                } else {
                    message.error(err?.response?.data?.message ?? err.message)
                }
            } finally {
                this.loading = false
            }
        },
    },
    mounted() {
        if (this.worker) {
            this.data = JSON.parse(JSON.stringify(this.worker))
        }
    },
}
</script>