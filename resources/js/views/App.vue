<template>
    <div class="wrapper">
        <Scheduler/>
    </div>
</template>

<script>
import Scheduler from './Scheduler/Scheduler.vue'

export default {
    components: {
        Scheduler,
    },
}
</script>

<style>
.wrapper {
    padding: 20px;
}
</style>