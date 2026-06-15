<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    agenda: Object,
    details: Array, // items checklist
});

const searchQuery = ref('');
const savingRows = ref({});

// filter checklist
const filteredDetails = computed(() => {
    if (!searchQuery.value) return props.details;
    const query = searchQuery.value.toLowerCase();
    return props.details.filter(d => 
        d.item_name.toLowerCase().includes(query) || 
        d.item_code.toLowerCase().includes(query) || 
        d.location.toLowerCase().includes(query)
    );
});

const saveRow = (detail) => {
    savingRows.value[detail.id] = true;
    useForm({
        physical_quantity: detail.physical_qty,
        notes: detail.notes,
    }).put(route('stock-opname.details.update', detail.id), {
        preserveScroll: true,
        onSuccess: () => {
            savingRows.value[detail.id] = false;
        },
        onError: () => {
            savingRows.value[detail.id] = false;
        }
    });
};

// Simulate barcode scanner scan
const simulateScan = () => {
    // Find first item where physical count doesn't match or is 0, or just pick the first one
    const unscanned = props.details.find(d => d.physical_qty === 0) || props.details[0];
    
    if (unscanned) {
        unscanned.physical_qty += 1;
        unscanned.notes = (unscanned.notes || '') + ' [SCANNED]';
        saveRow(unscanned);
        alert(`Simulasi Scan Sukses!\nBarang: ${unscanned.item_name}\nJumlah SO bertambah menjadi: ${unscanned.physical_qty}`);
    } else {
        alert('Semua barang telah discan.');
    }
};

const finalizeAudit = () => {
    if (confirm('Apakah Anda yakin ingin menyelesaikan Stock Opname ini?\nIni akan menyesuaikan stok fisik di database agar sesuai dengan Lembar SO ini.')) {
        useForm({}).post(route('stock-opname.finalize', props.agenda.id));
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};
</script>

<template>
    <Head title="Lembar Stock Opname" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <Link :href="route('stock-opname.index')" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200 font-mono">
                        {{ agenda.title }}
                    </h2>
                </div>
                <div class="space-x-2" v-if="agenda.status === 'draft'">
                    <button
                        type="button"
                        @click="simulateScan"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500"
                    >
                        [🔍] Simulasi Scan
                    </button>
                    <button
                        v-if="$page.props.auth.user.role === 'admin'"
                        type="button"
                        @click="finalizeAudit"
                        class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500"
                    >
                        Selesaikan & Sesuaikan Stok
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                <!-- Info Summary Panel -->
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 px-6 py-4">
                        <h3 class="text-lg font-medium text-gray-950 dark:text-white">Informasi Stock Opname</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                        <div class="space-y-2">
                            <p class="text-gray-500 dark:text-gray-400">Nama Unit: <span class="font-bold text-gray-900 dark:text-white">{{ agenda.unit_name }}</span></p>
                            <p class="text-gray-500 dark:text-gray-400">Pelaksana: <span class="font-bold text-gray-900 dark:text-white">{{ agenda.checked_by }}</span></p>
                            <p class="text-gray-500 dark:text-gray-400">Tanggal SO: <span class="font-bold text-gray-900 dark:text-white font-mono">{{ agenda.date }}</span></p>
                        </div>
                        <div class="space-y-2">
                            <p class="text-gray-500 dark:text-gray-400">Jumlah Lokasi: <span class="font-bold text-gray-900 dark:text-white">{{ agenda.total_locations }} Lokasi</span></p>
                            <p class="text-gray-500 dark:text-gray-400">Jumlah Barang (Sistem): <span class="font-bold text-gray-900 dark:text-white">{{ agenda.total_system_qty }} unit</span></p>
                            <p class="text-gray-500 dark:text-gray-400">Jumlah Barang (Fisik): <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ agenda.total_physical_qty }} unit</span></p>
                        </div>
                        <div class="flex flex-col justify-center items-center p-4 bg-indigo-50/50 dark:bg-indigo-950/10 rounded-lg">
                            <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-400 uppercase tracking-wider mb-1">Audit Progress</span>
                            <span class="text-3xl font-bold text-indigo-600 dark:text-indigo-300 font-mono">{{ agenda.percentage }}%</span>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-2">
                                <div class="bg-indigo-600 h-1.5 rounded-full" :style="{ width: agenda.percentage + '%' }"></div>
                            </div>
                        </div>
                        <div class="md:col-span-3 border-t border-gray-100 dark:border-gray-700 pt-4" v-if="agenda.notes">
                            <p class="text-gray-500 dark:text-gray-400">Catatan: <span class="text-gray-750 dark:text-gray-300 italic">{{ agenda.notes }}</span></p>
                        </div>
                    </div>
                </div>

                <!-- Checklist Table -->
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Search and Controls -->
                        <div class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-4 md:space-y-0">
                            <div class="w-full md:w-80">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari lokasi atau nama barang..."
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white"
                                />
                            </div>
                            <div class="flex space-x-2 text-xs">
                                <button type="button" class="px-3 py-1.5 rounded border border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 bg-white dark:bg-gray-800 font-semibold" @click="alert('Fitur Eksport sedang dipersiapkan.')">Eksport PDF</button>
                                <button type="button" class="px-3 py-1.5 rounded border border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 bg-white dark:bg-gray-800 font-semibold" @click="alert('Fitur Import sedang dipersiapkan.')">Import Excel</button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                                        <th class="px-4 py-3 text-left w-12">No</th>
                                        <th class="px-4 py-3 text-left">Lokasi</th>
                                        <th class="px-4 py-3 text-left">Nama Barang</th>
                                        <th class="px-4 py-3 text-left w-20">Satuan</th>
                                        <th class="px-4 py-3 text-right w-24">Harga (Rp.)</th>
                                        <th class="px-4 py-3 text-right w-24">Sistem Qty</th>
                                        <th class="px-4 py-3 text-left w-36">Jumlah SO</th>
                                        <th class="px-4 py-3 text-left">Selisih</th>
                                        <th class="px-4 py-3 text-left min-w-[200px]">Catatan</th>
                                        <th class="px-4 py-3 text-center w-20" v-if="agenda.status === 'draft'">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr 
                                        v-for="(detail, index) in filteredDetails" 
                                        :key="detail.id" 
                                        :class="[
                                            'text-sm hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors',
                                            detail.physical_qty !== detail.system_qty 
                                                ? 'bg-amber-50/10 dark:bg-amber-950/5' 
                                                : ''
                                        ]"
                                    >
                                        <td class="px-4 py-3 whitespace-nowrap">{{ index + 1 }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap font-medium">{{ detail.location }}</td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-gray-900 dark:text-white">{{ detail.item_name }}</div>
                                            <div class="text-xxs font-mono text-gray-400">{{ detail.item_code }} (exp: {{ detail.expiry }})</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ detail.unit_name }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right font-mono">{{ formatCurrency(detail.price) }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right font-bold text-gray-600 dark:text-gray-450">{{ detail.system_qty }}</td>
                                        
                                        <!-- Physical count inputs -->
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <input
                                                v-model.number="detail.physical_qty"
                                                type="number"
                                                min="0"
                                                :disabled="agenda.status === 'completed'"
                                                class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white py-1 px-2"
                                            />
                                        </td>

                                        <!-- Discrepancy indicator -->
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span 
                                                v-if="detail.physical_qty === detail.system_qty"
                                                class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/10 dark:bg-emerald-950/20 dark:text-emerald-400"
                                            >
                                                Match (0)
                                            </span>
                                            <span 
                                                v-else
                                                class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-600/10 dark:bg-rose-950/20 dark:text-rose-400"
                                            >
                                                Selisih ({{ detail.physical_qty - detail.system_qty }})
                                            </span>
                                        </td>

                                        <!-- Notes count input -->
                                        <td class="px-4 py-3">
                                            <input
                                                v-model="detail.notes"
                                                type="text"
                                                placeholder="Catatan selisih..."
                                                :disabled="agenda.status === 'completed'"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-750 dark:border-gray-600 dark:text-white py-1 px-2"
                                            />
                                        </td>

                                        <!-- Action -->
                                        <td class="px-4 py-3 whitespace-nowrap text-center text-xs" v-if="agenda.status === 'draft'">
                                            <button
                                                type="button"
                                                @click="saveRow(detail)"
                                                :disabled="savingRows[detail.id]"
                                                class="rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-950/20 dark:text-indigo-400 hover:bg-indigo-100 font-semibold px-2.5 py-1.5"
                                            >
                                                {{ savingRows[detail.id] ? 'Menyimpan...' : 'Simpan' }}
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredDetails.length === 0">
                                        <td colspan="10" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">Tidak ada barang yang cocok dengan pencarian.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
