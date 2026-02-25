<template>

    <Modal 
        :open="true"
        :mask="false"
        :closable="false"
        :footer="null">

        <Flex 
            :vertical="true"
            :gap="25">

            <Form layout="vertical">

                <FormItem
                    label="Email"
                    :required="true"
                    has-feedback
                    :validate-status="errors['email'] ? 'error' : ''"
                    :help="errors?.email">
                    <Input
                        placeholder="Enter email"
                        v-model:value="credentials.email"/>
                </FormItem>

                <FormItem
                    label="Password"
                    :required="true"
                    has-feedback
                    :validate-status="errors['password'] ? 'error' : ''"
                    :help="errors?.password">
                    <InputPassword
                        placeholder="Enter password"
                        v-model:value="credentials.password"/>
                </FormItem>

                <Button
                    :loading="loading"
                    @click="login">
                    Login
                </Button>
                
            </Form>

        </Flex>

    </Modal>

</template>

<script>
import { Modal, Form, FormItem, Input, Button, message, InputPassword, Flex, } from 'ant-design-vue'
import authApi from '../../api/auth'
import {hasRole } from '../../helpers/helpers'

export default {
    components: {
        Modal, Form, FormItem, 
        Input, Button, InputPassword,
        Flex,
    },
    data() {
        return {
            credentials: {
                email: null,
                password: null,
            },
            errors: {},
            loading: false,
        }
    },
    methods: {
        async login() {
            try {
                this.loading = true
                this.errors = {}
                await authApi.login(this.credentials)

                if (hasRole(['worker'])) {
                    location.href = 'worker-scheduler'
                } else {
                    location.href = '/'
                }
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