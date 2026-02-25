import { createRouter, createWebHistory, } from 'vue-router'
import { defineAsyncComponent, } from 'vue'
import Loader from '../views/components/Loader.vue'
import authApi from '../api/auth'
import { hasRole } from '../helpers/helpers'
import { message, } from 'ant-design-vue'

const routes = [{
    path: '/login',
    component: defineAsyncComponent({
        loader: () =>
            import ('../views/Auth/Login.vue'),
        loadingComponent: Loader,
    }),
    name: 'login',
    meta: {
        public: true,
        title: 'Login',
    },
}, {
    path: '/',
    component: defineAsyncComponent({
        loader: () =>
            import ('../views/Scheduler/Scheduler.vue'),
        loadingComponent: Loader,
    }),
    name: 'scheduler',
    meta: {
        roles: ['admin',],
        title: 'Scheduler',
    }
}, {
    path: '/worker-scheduler',
    component: defineAsyncComponent({
        loader: () =>
            import ('../views/Workers/Scheduler.vue'),
        loadingComponent: Loader,
    }),
    name: 'workers.scheduler',
    meta: {
        roles: ['worker',],
        title: 'Scheduler',
    },
}, ]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach((to, from) => {
    if (!to.meta.public && !authApi.isLogged()) {
        router.push({ name: 'login' })
        return false
    }

    if (to.meta.roles && !hasRole(to.meta.roles)) {
        message.error('Forbidden.')
        return false
    }

    document.title = `${to.meta.title}`
})

export default router