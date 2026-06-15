<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Array,
});

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const editingUser = ref(null);

const addForm = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    role: 'operator',
    jabatan: '',
    departemen: 'Gudang',
});

const editForm = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    role: 'operator',
    jabatan: '',
    departemen: 'Gudang',
});

const openAddModal = () => {
    addForm.reset();
    isAddModalOpen.value = true;
};

const submitAdd = () => {
    addForm.post(route('users.store'), {
        onSuccess: () => {
            isAddModalOpen.value = false;
            addForm.reset();
        },
    });
};

const openEditModal = (user) => {
    editingUser.value = user;
    editForm.name = user.name;
    editForm.username = user.username;
    editForm.email = user.email;
    editForm.password = '';
    editForm.role = user.role;
    editForm.jabatan = user.jabatan || '';
    editForm.departemen = user.departemen || 'Gudang';
    isEditModalOpen.value = true;
};

const submitEdit = () => {
    editForm.put(route('users.update', editingUser.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            editForm.reset();
        },
    });
};

const deleteUser = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
        useForm({}).delete(route('users.destroy', id));
    }
};
</script>

<template>
    <Head title="Manajemen User" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Manajemen User
                </h2>
                <button
                    @click="openAddModal"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                >
                    + Tambah User
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- User List -->
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                                        <th class="px-6 py-3 text-left">Nama</th>
                                        <th class="px-6 py-3 text-left">Username</th>
                                        <th class="px-6 py-3 text-left">Email</th>
                                        <th class="px-6 py-3 text-left">Role</th>
                                        <th class="px-6 py-3 text-left">Jabatan</th>
                                        <th class="px-6 py-3 text-left">Departemen</th>
                                        <th class="px-6 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="user in users" :key="user.id" class="text-sm text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-750">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ user.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap font-mono">{{ user.username }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ user.email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="[
                                                    'inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold ring-1 ring-inset',
                                                    user.role === 'admin' 
                                                        ? 'bg-red-50 text-red-700 ring-red-650/10 dark:bg-red-950/20 dark:text-red-400' 
                                                        : 'bg-green-50 text-green-700 ring-green-650/10 dark:bg-green-950/20 dark:text-green-400'
                                                ]"
                                            >
                                                {{ user.role === 'admin' ? 'Admin' : 'Operator' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ user.jabatan || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ user.departemen || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                            <button @click="openEditModal(user)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900">Edit</button>
                                            <button 
                                                v-if="$page.props.auth.user.id !== user.id" 
                                                @click="deleteUser(user.id)" 
                                                class="text-red-600 dark:text-red-400 hover:text-red-900"
                                            >
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add User Modal -->
        <div v-if="isAddModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tambah Data User</h3>
                    <button @click="isAddModalOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <form @submit.prevent="submitAdd" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap *</label>
                        <input v-model="addForm.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="addForm.errors.name" class="mt-1 text-xs text-red-600">{{ addForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username *</label>
                        <input v-model="addForm.username" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="addForm.errors.username" class="mt-1 text-xs text-red-600">{{ addForm.errors.username }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email *</label>
                        <input v-model="addForm.email" type="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="addForm.errors.email" class="mt-1 text-xs text-red-600">{{ addForm.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password *</label>
                        <input v-model="addForm.password" type="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="addForm.errors.password" class="mt-1 text-xs text-red-600">{{ addForm.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role *</label>
                        <select v-model="addForm.role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                            <option value="admin">Admin</option>
                            <option value="operator">Operator</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan</label>
                        <input v-model="addForm.jabatan" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Departemen</label>
                        <select v-model="addForm.departemen" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                            <option value="Produksi">Produksi</option>
                            <option value="Gudang">Gudang</option>
                            <option value="IT">IT</option>
                            <option value="Logistics">Logistics</option>
                        </select>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="isAddModalOpen = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Tutup</button>
                        <button type="submit" :disabled="addForm.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Edit Data User</h3>
                    <button @click="isEditModalOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap *</label>
                        <input v-model="editForm.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="editForm.errors.name" class="mt-1 text-xs text-red-600">{{ editForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username *</label>
                        <input v-model="editForm.username" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="editForm.errors.username" class="mt-1 text-xs text-red-600">{{ editForm.errors.username }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email *</label>
                        <input v-model="editForm.email" type="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="editForm.errors.email" class="mt-1 text-xs text-red-600">{{ editForm.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password (Kosongkan jika tidak diubah)</label>
                        <input v-model="editForm.password" type="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="editForm.errors.password" class="mt-1 text-xs text-red-600">{{ editForm.errors.password }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role *</label>
                        <select v-model="editForm.role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                            <option value="admin">Admin</option>
                            <option value="operator">Operator</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan</label>
                        <input v-model="editForm.jabatan" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Departemen</label>
                        <select v-model="editForm.departemen" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                            <option value="Produksi">Produksi</option>
                            <option value="Gudang">Gudang</option>
                            <option value="IT">IT</option>
                            <option value="Logistics">Logistics</option>
                        </select>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="isEditModalOpen = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Tutup</button>
                        <button type="submit" :disabled="editForm.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
