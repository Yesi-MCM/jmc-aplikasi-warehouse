<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    racks: Array,
    units: Array, // units with loaded rooms
});

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const editingRack = ref(null);

const addForm = useForm({
    unit_id: '',
    room_id: '',
    code: '',
    name: '',
    max_tiers: 6,
    description: '',
});

const editForm = useForm({
    unit_id: '',
    room_id: '',
    code: '',
    name: '',
    max_tiers: 6,
    description: '',
});

// Computed list of rooms based on selected Unit in add/edit forms
const addRooms = computed(() => {
    if (!addForm.unit_id) return [];
    const selectedUnit = props.units.find(u => u.id === parseInt(addForm.unit_id));
    return selectedUnit ? selectedUnit.rooms : [];
});

const editRooms = computed(() => {
    if (!editForm.unit_id) return [];
    const selectedUnit = props.units.find(u => u.id === parseInt(editForm.unit_id));
    return selectedUnit ? selectedUnit.rooms : [];
});

const openAddModal = () => {
    addForm.reset();
    isAddModalOpen.value = true;
};

const submitAdd = () => {
    addForm.post(route('racks.store'), {
        onSuccess: () => {
            isAddModalOpen.value = false;
            addForm.reset();
        },
    });
};

const openEditModal = (rack) => {
    editingRack.value = rack;
    editForm.unit_id = rack.room.unit_id;
    editForm.room_id = rack.room_id;
    editForm.code = rack.code;
    editForm.name = rack.name;
    editForm.max_tiers = rack.max_tiers;
    editForm.description = rack.description || '';
    isEditModalOpen.value = true;
};

const submitEdit = () => {
    editForm.put(route('racks.update', editingRack.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            editForm.reset();
        },
    });
};

const deleteRack = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus rak ini? Seluruh stok yang tersimpan di rak ini juga akan terhapus.')) {
        useForm({}).delete(route('racks.destroy', id));
    }
};
</script>

<template>
    <Head title="Manajemen Penyimpanan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Manajemen Penyimpanan (Rak)
                </h2>
                <button
                    @click="openAddModal"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                >
                    + Tambah Rak
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Racks Table -->
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                                        <th class="px-6 py-3 text-left">Unit Gudang</th>
                                        <th class="px-6 py-3 text-left">Ruang / Lokasi</th>
                                        <th class="px-6 py-3 text-left">Kode Rak</th>
                                        <th class="px-6 py-3 text-left">Nama Rak</th>
                                        <th class="px-6 py-3 text-left">Jumlah Tingkat</th>
                                        <th class="px-6 py-3 text-left">Keterangan</th>
                                        <th class="px-6 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="rack in racks" :key="rack.id" class="text-sm text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-750">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ rack.room.unit.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ rack.room.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap font-mono">{{ rack.code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ rack.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ rack.max_tiers }} Tingkat</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ rack.description || '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium space-x-2">
                                            <button @click="openEditModal(rack)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900">Edit</button>
                                            <button @click="deleteRack(rack.id)" class="text-red-600 dark:text-red-400 hover:text-red-900">Hapus</button>
                                        </td>
                                    </tr>
                                    <tr v-if="racks.length === 0">
                                        <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">Belum ada data rak penyimpanan.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Rack Modal -->
        <div v-if="isAddModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tambah Data Rak</h3>
                    <button @click="isAddModalOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <form @submit.prevent="submitAdd" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit Gudang *</label>
                        <select v-model="addForm.unit_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih Unit</option>
                            <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ruang / Lokasi *</label>
                        <select v-model="addForm.room_id" required :disabled="!addForm.unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih Lokasi</option>
                            <option v-for="r in addRooms" :key="r.id" :value="r.id">{{ r.name }}</option>
                        </select>
                        <p v-if="addForm.errors.room_id" class="mt-1 text-xs text-red-600">{{ addForm.errors.room_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Rak *</label>
                        <input v-model="addForm.code" type="text" required placeholder="Contoh: RA01, S01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="addForm.errors.code" class="mt-1 text-xs text-red-600">{{ addForm.errors.code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Rak *</label>
                        <input v-model="addForm.name" type="text" required placeholder="Contoh: Rak S01, Rak RA01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="addForm.errors.name" class="mt-1 text-xs text-red-600">{{ addForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah Tingkat *</label>
                        <select v-model="addForm.max_tiers" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                            <option v-for="n in 20" :key="n" :value="n">{{ n }}</option>
                        </select>
                        <p v-if="addForm.errors.max_tiers" class="mt-1 text-xs text-red-600">{{ addForm.errors.max_tiers }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan</label>
                        <textarea v-model="addForm.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white"></textarea>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="isAddModalOpen = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Tutup</button>
                        <button type="submit" :disabled="addForm.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Rack Modal -->
        <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Edit Data Rak</h3>
                    <button @click="isEditModalOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit Gudang *</label>
                        <select v-model="editForm.unit_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih Unit</option>
                            <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ruang / Lokasi *</label>
                        <select v-model="editForm.room_id" required :disabled="!editForm.unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih Lokasi</option>
                            <option v-for="r in editRooms" :key="r.id" :value="r.id">{{ r.name }}</option>
                        </select>
                        <p v-if="editForm.errors.room_id" class="mt-1 text-xs text-red-600">{{ editForm.errors.room_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Rak *</label>
                        <input v-model="editForm.code" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="editForm.errors.code" class="mt-1 text-xs text-red-600">{{ editForm.errors.code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Rak *</label>
                        <input v-model="editForm.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="editForm.errors.name" class="mt-1 text-xs text-red-600">{{ editForm.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah Tingkat *</label>
                        <select v-model="editForm.max_tiers" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                            <option v-for="n in 20" :key="n" :value="n">{{ n }}</option>
                        </select>
                        <p v-if="editForm.errors.max_tiers" class="mt-1 text-xs text-red-600">{{ editForm.errors.max_tiers }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan</label>
                        <textarea v-model="editForm.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white"></textarea>
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
