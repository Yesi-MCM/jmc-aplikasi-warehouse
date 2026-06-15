<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    agendas: Array,
    units: Array,
});

const isModalOpen = ref(false);

const form = useForm({
    title: '',
    unit_id: '',
    date: new Date().toISOString().split('T')[0],
    checked_by: '',
    notes: '',
    attachment: null,
});

const submitForm = () => {
    form.post(route('stock-opname.store'), {
        forceFormData: true,
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        }
    });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Stock Opname" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Stock Opname (Audit Inventaris)
                </h2>
                <button
                    @click="isModalOpen = true"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                >
                    + Buat Agenda Stock Opname
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Agenda List Table -->
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                                        <th class="px-6 py-3 text-left w-12">No</th>
                                        <th class="px-6 py-3 text-left">Tanggal</th>
                                        <th class="px-6 py-3 text-left">Judul Agenda</th>
                                        <th class="px-6 py-3 text-left">Unit Gudang</th>
                                        <th class="px-6 py-3 text-left">Pelaksana</th>
                                        <th class="px-6 py-3 text-right">Progress</th>
                                        <th class="px-6 py-3 text-center">Status</th>
                                        <th class="px-6 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="(agenda, index) in agendas" :key="agenda.id" class="text-sm text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-750">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(agenda.date) }}</td>
                                        <td class="px-6 py-4 font-semibold">{{ agenda.title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ agenda.unit }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ agenda.checked_by }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end space-x-2">
                                                <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                    <div class="bg-indigo-600 h-2 rounded-full" :style="{ width: agenda.percentage + '%' }"></div>
                                                </div>
                                                <span class="font-semibold">{{ agenda.percentage }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span
                                                :class="[
                                                    'inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset',
                                                    agenda.status === 'completed'
                                                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/10 dark:bg-emerald-950/20 dark:text-emerald-400'
                                                        : 'bg-amber-50 text-amber-700 ring-amber-600/10 dark:bg-amber-950/20 dark:text-amber-400'
                                                ]"
                                            >
                                                {{ agenda.status === 'completed' ? 'Selesai' : 'Draf / Proses' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center font-medium">
                                            <Link :href="route('stock-opname.show', agenda.id)" class="text-indigo-650 dark:text-indigo-400 hover:text-indigo-900">
                                                Buka Lembar SO
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="agendas.length === 0">
                                        <td colspan="8" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">Belum ada agenda stock opname.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Agenda Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Buat Agenda Stock Opname</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-650 text-xl font-bold">&times;</button>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul Agenda *</label>
                        <input v-model="form.title" type="text" required placeholder="Contoh: Stock Opname Juni 2026" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="form.errors.title" class="mt-1 text-xs text-red-650">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit Gudang Audit *</label>
                        <select v-model="form.unit_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih Unit</option>
                            <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                        <p v-if="form.errors.unit_id" class="mt-1 text-xs text-red-650">{{ form.errors.unit_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Pelaksanaan *</label>
                        <input v-model="form.date" type="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="form.errors.date" class="mt-1 text-xs text-red-650">{{ form.errors.date }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pelaksana (Checked By) *</label>
                        <input v-model="form.checked_by" type="text" required placeholder="Contoh: John Doe, Budi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                        <p v-if="form.errors.checked_by" class="mt-1 text-xs text-red-650">{{ form.errors.checked_by }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lampiran Dokumen Tambahan</label>
                        <input @input="form.attachment = $event.target.files[0]" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                        <p v-if="form.errors.attachment" class="mt-1 text-xs text-red-650">{{ form.errors.attachment }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan Tambahan</label>
                        <textarea v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white"></textarea>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="isModalOpen = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Tutup</button>
                        <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-650 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-550">Mulai SO</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
