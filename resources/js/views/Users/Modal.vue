<template>
    <Modal 
        :title="title"
        :open="open"
        :footer="null"
        @cancel="$emit('update:open', false)">

        <Form layout="vertical">

            <FormItem
                label="Email"
                :required="true"
                has-feedback
                :validate-status="errors['email'] ? 'error' : ''"
                :help="errors?.email">
                <Input
                    placeholder="Enter email"
                    v-model:value="data.email"/>
            </FormItem>

            <FormItem
                label="Password"
                :required="true"
                has-feedback
                :validate-status="errors['password'] ? 'error' : ''"
                :help="errors?.password">
                <InputPassword
                    placeholder="Enter password"
                    v-model:value="data.password"/>
            </FormItem>

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
                label="Role"
                :required="true"
                has-feedback
                :validate-status="errors['role'] ? 'error' : ''"
                :help="errors.role">
                <Select
                    placeholder="Enter role"
                    v-model:value="data.role"
                    :options="roleOptions"/>
            </FormItem>

            <FormItem
                v-if="data.role == 'worker'"
                label="Color"
                has-feedback
                :validate-status="errors['color'] ? 'error' : ''"
                :help="errors.color">
                <CompactPicker v-model="data.color"/>
            </FormItem>

            <Button
                :loading="loading"
                @click="action == 'create' ? create() : null">
                Save
            </Button>

        </Form>

    </Modal>
</template>

<script>
import { Modal, Button, Form, FormItem, Input, message, InputPassword, Select, } from 'ant-design-vue'
import usersApi from '../../api/users'
import 'vue-color/style.css'
import { CompactPicker } from 'vue-color'

export default {
    props: [
        'title',
        'open',
        'action',
    ],
    components: {
        Modal, Button, Form, 
        FormItem, Input, CompactPicker,
        InputPassword, Select,
    },
    data() {
        return {
            data: {
                email: '',
                password: '',
                name: '',
                role: 'worker',
                color: '#4d4d4d',
            },
            errors: {},
            loading: false,
            roles: [
                'admin',
                'worker',
            ],
        }
    },    
    computed: {
        roleOptions() {
            return this.roles.map(role => ({value: role}))
        },
    },
    methods: {
        async create() {
            try {
                this.loading = true
                this.errors = {}
                const data = await usersApi.create(this.data)
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
    },
}
</script>