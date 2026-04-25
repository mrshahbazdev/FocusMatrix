<script setup>
import FocusLayout from '@/Layouts/FocusLayout.vue';
import DeleteTeamForm from '@/Pages/Teams/Partials/DeleteTeamForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TeamMemberManager from '@/Pages/Teams/Partials/TeamMemberManager.vue';
import UpdateTeamNameForm from '@/Pages/Teams/Partials/UpdateTeamNameForm.vue';

defineProps({
    team: Object,
    availableRoles: Array,
    permissions: Object,
});
</script>

<template>
    <FocusLayout>
        <div class="max-w-4xl mx-auto space-y-10">
            <div>
                <h1 class="font-display text-2xl font-bold text-navy-900">Team Settings</h1>
                <p class="mt-1 text-sm text-graphite-500">Manage your team name, members, and permissions.</p>
            </div>

            <UpdateTeamNameForm :team="team" :permissions="permissions" />

            <TeamMemberManager
                class="mt-10 sm:mt-0"
                :team="team"
                :available-roles="availableRoles"
                :user-permissions="permissions"
            />

            <template v-if="permissions.canDeleteTeam && ! team.personal_team">
                <SectionBorder />
                <DeleteTeamForm class="mt-10 sm:mt-0" :team="team" />
            </template>
        </div>
    </FocusLayout>
</template>
