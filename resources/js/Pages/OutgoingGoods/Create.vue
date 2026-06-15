<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    units: Array,
});

const form = useForm({
    unit_id: '',
    receiver_name: '',
    contact_person: '',
    phone_number: '',
    notes: '',
    items: [
        {
            item_id: '',
            quantity: 1,
            // UI display fields
            code: '',
            est_price: 0,
            available_qty: 0,
            unit_name: 'buah',
        }
    ],
});

const availableItems = ref([]);

// Fetch available stocks for the selected Unit
watch(() => form.unit_id, async (newUnitId) => {
    // Reset items when Unit changes
    form.items = [
        {
            item_id: '',
            quantity: 1,
            code: '',
            est_price: 0,
            available_qty: 0,
            unit_name: 'buah',
        }
    ];

    if (!newUnitId) {
        availableItems.value = [];
        return;
    }

    try {
        const response = await axios.get(route('api.units.available-items', newUnitId));
        availableItems.value = response.data;
    } catch (e) {
        console.error('Error fetching available stocks', e);
        availableItems.value = [];
    }
});

const onItemSelectChange = (index) => {
    const selectedItem = availableItems.value.find(i => i.id === parseInt(form.items[index].item_id));
    if (selectedItem) {
        form.items[index].code = selectedItem.code;
        form.items[index].est_price = selectedItem.est_price;
        form.items[index].available_qty = selectedItem.available_quantity;
        form.items[index].unit_name = selectedItem.unit_name;
    } else {
        form.items[index].code = '';
        form.items[index].est_price = 0;
        form.items[index].available_qty = 0;
        form.items[index].unit_name = 'buah';
    }
};

const addRow = () => {
    form.items.push({
        item_id: '',
        quantity: 1,
        code: '',
        est_price: 0,
        available_qty: 0,
        unit_name: 'buah',
    });
};

const removeRow = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const submitForm = () => {
    // Validate quantities before submitting
    const invalidQty = form.items.some(item => item.quantity > item.available_qty);
    if (invalidQty) {
        alert('Terdapat barang dengan jumlah melebihi stok yang tersedia.');
        return;
    }

    form.post(route('outgoing-goods.store'));
};
</script>

<template>
    <Head title="Barang Keluar" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Formulir Barang Keluar (Pengeluaran)
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Errors -->
                <div v-if="form.errors.error" class="mb-6 bg-red-50 dark:bg-red-950/20 border-l-4 border-red-500 p-4 text-red-700 dark:text-red-400 rounded-md">
                    {{ form.errors.error }}
                </div>

                <form @submit.prevent="submitForm" class="space-y-8">
                    <!-- General Info -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-950 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-3">Informasi Umum</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit Gudang Asal *</label>
                                <select v-model="form.unit_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                                    <option value="">Pilih Unit Asal</option>
                                    <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                                </select>
                                <p v-if="form.errors.unit_id" class="mt-1 text-xs text-red-600">{{ form.errors.unit_id }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Penerima Barang *</label>
                                <input v-model="form.receiver_name" type="text" required placeholder="Contoh: PT. DELTANET" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                                <p v-if="form.errors.receiver_name" class="mt-1 text-xs text-red-600">{{ form.errors.receiver_name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Person *</label>
                                <input v-model="form.contact_person" type="text" required placeholder="Contoh: Delta Pranata Putra" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                                <p v-if="form.errors.contact_person" class="mt-1 text-xs text-red-600">{{ form.errors.contact_person }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Telepon/HP *</label>
                                <input v-model="form.phone_number" type="text" required placeholder="Contoh: 081234567890" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                                <p v-if="form.errors.phone_number" class="mt-1 text-xs text-red-600">{{ form.errors.phone_number }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                                <textarea v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Items Detail Table -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6 border-b border-gray-100 dark:border-gray-700 pb-3">
                            <h3 class="text-lg font-medium text-gray-950 dark:text-white">Informasi Barang</h3>
                            <button
                                type="button"
                                @click="addRow"
                                :disabled="!form.unit_id"
                                class="rounded-md bg-indigo-650 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-550 shadow-sm disabled:opacity-50"
                            >
                                + Tambah Baris
                            </button>
                        </div>

                        <div v-if="!form.unit_id" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            Silakan pilih Unit Gudang Asal terlebih dahulu untuk memuat stok barang yang tersedia.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                                        <th class="px-3 py-2 text-left min-w-[250px]">Nama Barang</th>
                                        <th class="px-3 py-2 text-left w-44">Est. Harga (Rp.)</th>
                                        <th class="px-3 py-2 text-left w-32">Jumlah</th>
                                        <th class="px-3 py-2 text-left w-24">Satuan</th>
                                        <th class="px-3 py-2 text-left w-44">Total (Rp.)</th>
                                        <th class="px-3 py-2 text-center w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="(item, index) in form.items" :key="index" class="text-sm">
                                        <!-- Nama Barang -->
                                        <td class="px-3 py-3">
                                            <select v-model="item.item_id" @change="onItemSelectChange(index)" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                                                <option value="">Pilih Barang</option>
                                                <option v-for="i in availableItems" :key="i.id" :value="i.id">[{{ i.code }}] {{ i.name }} (Stok: {{ i.available_quantity }})</option>
                                            </select>
                                        </td>
                                        <!-- Est. Harga -->
                                        <td class="px-3 py-3">
                                            <div class="relative rounded-md shadow-sm">
                                                <input v-model="item.est_price" type="number" readonly class="block w-full bg-gray-50 border-gray-300 rounded-md focus:ring-0 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-400" />
                                            </div>
                                        </td>
                                        <!-- Jumlah -->
                                        <td class="px-3 py-3">
                                            <input v-model.number="item.quantity" type="number" required min="1" :max="item.available_qty" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                                            <span v-if="item.available_qty > 0 && item.quantity > item.available_qty" class="text-xxs text-red-650 font-semibold mt-1 block">Max: {{ item.available_qty }}</span>
                                        </td>
                                        <!-- Satuan -->
                                        <td class="px-3 py-3">
                                            <input v-model="item.unit_name" type="text" required readonly class="block w-full bg-gray-50 border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-400" />
                                        </td>
                                        <!-- Total -->
                                        <td class="px-3 py-3 font-semibold font-mono text-gray-750 dark:text-gray-300">
                                            {{ formatCurrency(item.est_price * item.quantity) }}
                                        </td>
                                        <!-- Delete row -->
                                        <td class="px-3 py-3 text-center">
                                            <button
                                                type="button"
                                                @click="removeRow(index)"
                                                v-if="form.items.length > 1"
                                                class="rounded-full p-1 text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Submit action -->
                    <div class="flex justify-end space-x-3 mt-6">
                        <Link :href="route('items.index')" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:border-gray-650 dark:text-gray-300 dark:bg-gray-800 dark:hover:bg-gray-700">
                            Batal
                        </Link>
                        <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-650 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-550">
                            Simpan Pengeluaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
