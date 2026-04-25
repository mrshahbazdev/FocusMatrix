<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link href="/" class="inline-flex items-center gap-2 font-display text-xl font-semibold">
        <span class="grid grid-cols-2 grid-rows-2 gap-0.5 w-8 h-8">
            <span class="bg-white/90 lg:bg-white/90"></span>
            <span class="bg-accent"></span>
            <span class="bg-navy-400 lg:bg-navy-400"></span>
            <span class="bg-white/90 lg:bg-white/90"></span>
        </span>
        <span class="text-navy-900 lg:text-white">Focus<span class="text-accent">Matrix</span></span>
    </Link>
</template>
