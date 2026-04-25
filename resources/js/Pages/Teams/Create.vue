<script setup>
import FocusLayout from '@/Layouts/FocusLayout.vue';
import CreateTeamForm from '@/Pages/Teams/Partials/CreateTeamForm.vue';
</script>

<template>
    <FocusLayout>
        <div class="max-w-4xl mx-auto space-y-10">
            <div>
                <h1 class="font-display text-2xl font-bold text-navy-900">Create Team</h1>
                <p class="mt-1 text-sm text-graphite-500">Set up a new team and invite your colleagues.</p>
            </div>

            <CreateTeamForm />
        </div>
    </FocusLayout>
</template>
