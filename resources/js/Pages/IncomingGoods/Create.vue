<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    items: Array, // master items
    units: Array, // units with rooms and racks
});

const isModalOpen = ref(false);
const activeRowIndex = ref(null);

const form = useForm({
    unit_id: '',
    vendor_name: '',
    contact_person: '',
    phone_number: '',
    invoice_number: '',
    attachment: null,
    notes: '',
    items: [
        {
            item_id: '',
            price: 0,
            quantity: 1,
            unit_name: 'Unit',
            expiry_date: '',
            room_id: '',
            rack_id: '',
            tier: '',
            // UI display fields
            item_name: '',
            location_label: '',
        }
    ],
});

// modal forms state
const modalState = ref({
    room_id: '',
    rack_id: '',
    tier: '',
});

// helper computed for selected Unit's rooms
const activeRooms = computed(() => {
    if (!form.unit_id) return [];
    const selectedUnit = props.units.find(u => u.id === parseInt(form.unit_id));
    return selectedUnit ? selectedUnit.rooms : [];
});

// helper computed for selected Room's racks
const activeRacks = computed(() => {
    if (!modalState.value.room_id) return [];
    const rooms = activeRooms.value;
    const selectedRoom = rooms.find(r => r.id === parseInt(modalState.value.room_id));
    return selectedRoom ? selectedRoom.racks : [];
});

// helper computed for selected Rack's max tiers
const activeMaxTiers = computed(() => {
    if (!modalState.value.rack_id) return 0;
    const racks = activeRacks.value;
    const selectedRack = racks.find(r => r.id === parseInt(modalState.value.rack_id));
    return selectedRack ? selectedRack.max_tiers : 0;
});

// dynamic options logic
const onUnitChange = () => {
    // reset allocations for all items
    form.items.forEach(item => {
        item.room_id = '';
        item.rack_id = '';
        item.tier = '';
        item.location_label = '';
    });
};

const onItemSelectChange = (index) => {
    const selectedItem = props.items.find(i => i.id === parseInt(form.items[index].item_id));
    if (selectedItem) {
        form.items[index].item_name = selectedItem.name;
        form.items[index].unit_name = selectedItem.unit_name;
    } else {
        form.items[index].item_name = '';
        form.items[index].unit_name = 'Unit';
    }
};

const addRow = () => {
    form.items.push({
        item_id: '',
        price: 0,
        quantity: 1,
        unit_name: 'Unit',
        expiry_date: '',
        room_id: '',
        rack_id: '',
        tier: '',
        item_name: '',
        location_label: '',
    });
};

const removeRow = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const openAllocationModal = (index) => {
    if (!form.unit_id) {
        alert('Silakan pilih Unit Gudang terlebih dahulu.');
        return;
    }
    
    const item = form.items[index];
    if (!item.item_id) {
        alert('Silakan pilih Nama Barang terlebih dahulu.');
        return;
    }

    activeRowIndex.value = index;
    modalState.value.room_id = item.room_id || '';
    modalState.value.rack_id = item.rack_id || '';
    modalState.value.tier = item.tier || '';
    isModalOpen.value = true;
};

const saveAllocation = () => {
    if (!modalState.value.room_id || !modalState.value.rack_id || !modalState.value.tier) {
        alert('Semua pilihan lokasi harus diisi.');
        return;
    }

    const index = activeRowIndex.value;
    const item = form.items[index];

    item.room_id = modalState.value.room_id;
    item.rack_id = modalState.value.rack_id;
    item.tier = modalState.value.tier;

    // Find labels
    const selectedRoom = activeRooms.value.find(r => r.id === parseInt(item.room_id));
    const selectedRack = activeRacks.value.find(r => r.id === parseInt(item.rack_id));

    item.location_label = `${selectedRoom.name}, ${selectedRack.name}, Tingkat ${item.tier}`;
    isModalOpen.value = false;
};

const submitForm = () => {
    // Validate that all items have location allocations
    const unallocated = form.items.some(item => !item.room_id || !item.rack_id || !item.tier);
    if (unallocated) {
        alert('Semua barang harus dialokasikan tempat penyimpanannya sebelum disimpan.');
        return;
    }

    form.post(route('incoming-goods.store'), {
        forceFormData: true, // required for files
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const activeUnitName = computed(() => {
    if (!form.unit_id) return '';
    const unit = props.units.find(u => u.id === parseInt(form.unit_id));
    return unit ? unit.name : '';
});
</script>

<template>
    <Head title="Barang Masuk" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Formulir Barang Masuk (Penerimaan)
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <form @submit.prevent="submitForm" class="space-y-8">
                    <!-- General Info -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-950 dark:text-white mb-6 border-b border-gray-100 dark:border-gray-700 pb-3">Informasi Umum</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit Gudang *</label>
                                <select v-model="form.unit_id" @change="onUnitChange" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                                    <option value="">Pilih Unit Gudang</option>
                                    <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                                </select>
                                <p v-if="form.errors.unit_id" class="mt-1 text-xs text-red-650">{{ form.errors.unit_id }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asal Barang / Vendor *</label>
                                <input v-model="form.vendor_name" type="text" required placeholder="Contoh: PT. Mandala Buku" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                                <p v-if="form.errors.vendor_name" class="mt-1 text-xs text-red-650">{{ form.errors.vendor_name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Person *</label>
                                <input v-model="form.contact_person" type="text" required placeholder="Contoh: Kevin Mandala" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                                <p v-if="form.errors.contact_person" class="mt-1 text-xs text-red-650">{{ form.errors.contact_person }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Telepon/HP *</label>
                                <input v-model="form.phone_number" type="text" required placeholder="Contoh: 08995008595" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                                <p v-if="form.errors.phone_number" class="mt-1 text-xs text-red-650">{{ form.errors.phone_number }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Surat *</label>
                                <input v-model="form.invoice_number" type="text" required placeholder="Contoh: 123" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                                <p v-if="form.errors.invoice_number" class="mt-1 text-xs text-red-650">{{ form.errors.invoice_number }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lampiran Dokumen (Max 8MB: doc, xls, pdf, jpg, png)</label>
                                <input @input="form.attachment = $event.target.files[0]" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                <p v-if="form.errors.attachment" class="mt-1 text-xs text-red-650">{{ form.errors.attachment }}</p>
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
                            <button type="button" @click="addRow" class="rounded-md bg-indigo-650 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-550 shadow-sm">
                                + Tambah Baris
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                                        <th class="px-3 py-2 text-left min-w-[200px]">Nama Barang</th>
                                        <th class="px-3 py-2 text-left w-36">Harga (Rp.)</th>
                                        <th class="px-3 py-2 text-left w-24">Jumlah</th>
                                        <th class="px-3 py-2 text-left w-24">Satuan</th>
                                        <th class="px-3 py-2 text-left w-36">Total (Rp.)</th>
                                        <th class="px-3 py-2 text-left w-40">Tanggal Kadaluarsa</th>
                                        <th class="px-3 py-2 text-left min-w-[150px]">Alokasi Penyimpanan</th>
                                        <th class="px-3 py-2 text-center w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="(item, index) in form.items" :key="index" class="text-sm">
                                        <!-- Nama Barang -->
                                        <td class="px-3 py-3">
                                            <select v-model="item.item_id" @change="onItemSelectChange(index)" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                                                <option value="">Pilih Barang</option>
                                                <option v-for="i in items" :key="i.id" :value="i.id">[{{ i.code }}] {{ i.name }}</option>
                                            </select>
                                        </td>
                                        <!-- Harga -->
                                        <td class="px-3 py-3">
                                            <input v-model.number="item.price" type="number" required min="0" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                                        </td>
                                        <!-- Jumlah -->
                                        <td class="px-3 py-3">
                                            <input v-model.number="item.quantity" type="number" required min="1" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                                        </td>
                                        <!-- Satuan -->
                                        <td class="px-3 py-3">
                                            <input v-model="item.unit_name" type="text" required readonly class="block w-full bg-gray-50 border-gray-300 rounded-md shadow-sm dark:bg-gray-900 dark:border-gray-700 dark:text-gray-400" />
                                        </td>
                                        <!-- Total -->
                                        <td class="px-3 py-3 whitespace-nowrap font-semibold font-mono text-gray-750 dark:text-gray-300">
                                            {{ formatCurrency(item.price * item.quantity) }}
                                        </td>
                                        <!-- Expired Date -->
                                        <td class="px-3 py-3">
                                            <input v-model="item.expiry_date" type="date" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white" />
                                        </td>
                                        <!-- Allocation trigger -->
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                <button
                                                    type="button"
                                                    @click="openAllocationModal(index)"
                                                    class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 ring-1 ring-inset ring-indigo-700/10 dark:bg-indigo-950/20 dark:text-indigo-400"
                                                >
                                                    {{ item.location_label ? 'Ubah Lokasi' : 'Alokasikan' }}
                                                </button>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[150px]" :title="item.location_label">
                                                    {{ item.location_label || 'Belum diatur *' }}
                                                </span>
                                            </div>
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
                            Simpan Penerimaan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lokasi Barang (Storage Allocation Modal) -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-indigo-50 dark:bg-indigo-950/20">
                    <h3 class="text-md font-bold text-indigo-900 dark:text-indigo-400">Lokasi Penyimpanan Barang</h3>
                    <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-650 text-xl font-bold">&times;</button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-450 dark:text-gray-500">Unit Gudang</label>
                        <input :value="activeUnitName" type="text" readonly class="mt-1 block w-full bg-gray-50 dark:bg-gray-900 border-gray-300 dark:border-gray-750 text-gray-500 rounded-md shadow-sm" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-450 dark:text-gray-500">Nama Barang</label>
                        <input :value="form.items[activeRowIndex].item_name" type="text" readonly class="mt-1 block w-full bg-gray-50 dark:bg-gray-900 border-gray-300 dark:border-gray-750 text-gray-500 rounded-md shadow-sm" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ruangan / Lokasi *</label>
                        <select v-model="modalState.room_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih Ruangan</option>
                            <option v-for="r in activeRooms" :key="r.id" :value="r.id">{{ r.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rak Penyimpanan *</label>
                        <select v-model="modalState.rack_id" :disabled="!modalState.room_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih Rak</option>
                            <option v-for="rk in activeRacks" :key="rk.id" :value="rk.id">{{ rk.name }} ({{ rk.code }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tingkat Rak *</label>
                        <select v-model="modalState.tier" :disabled="!modalState.rack_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih Tingkat</option>
                            <option v-for="t in activeMaxTiers" :key="t" :value="t">Tingkat {{ t }}</option>
                        </select>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3 border-t border-gray-100 dark:border-gray-700 pt-4">
                        <button type="button" @click="isModalOpen = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Tutup</button>
                        <button type="button" @click="saveAllocation" class="rounded-md bg-indigo-650 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-550">Simpan Alokasi</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
