<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PasswordChangeModal from '@/Components/PasswordChangeModal.vue';
import { ref } from 'vue';

const showPasswordModal = ref(false);
</script>

<template>
    <AdminLayout>
        <main class="flex flex-col gap-7 text-sm">
            <div class="flex justify-between items-center">
                <div class="flex gap-3 items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <img 
                            :src="'/Images/SVG/gear-six.svg'" 
                            alt="Settings Icon"
                            class="h-8"
                        >
                    </div>
                    <div>
                        <h1 class="font-semibold text-2xl font-[Poppins] text-gray-800">Settings</h1>
                        <p class="text-gray-600 text-sm">Manage CivicWatch settings</p>
                    </div>
                </div>
            </div>

            <!-- Security Section -->
            <div class="max-w-2xl">
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Security</h3>
                            <p class="text-sm text-gray-600">Manage CivicWatch security settings</p>
                        </div>
                        <button 
                            @click="showPasswordModal = true"
                            class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                        >
                            Change Password
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <PasswordChangeModal 
                :show="showPasswordModal" 
                @close="showPasswordModal = false" 
            />
        </main>
    </AdminLayout>
</template>