<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    items: Array,
    categories: Array,
});

const expandedRows = ref({});

const toggleRow = (itemId) => {
    expandedRows.value[itemId] = !expandedRows.value[itemId];
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};
</script>

<template>
    <Head title="Data Barang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Daftar Stok & Data Barang
                </h2>
                <div class="space-x-2">
                    <Link
                        :href="route('incoming-goods.create')"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        Barang Masuk
                    </Link>
                    <Link
                        :href="route('outgoing-goods.create')"
                        class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500"
                    >
                        Barang Keluar
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Items list -->
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                                        <th class="px-6 py-3 text-left w-12">No</th>
                                        <th class="px-6 py-3 text-left w-24">Action</th>
                                        <th class="px-6 py-3 text-left">Kategori</th>
                                        <th class="px-6 py-3 text-left">Kode</th>
                                        <th class="px-6 py-3 text-left">Nama</th>
                                        <th class="px-6 py-3 text-right">Harga (Rp.)</th>
                                        <th class="px-6 py-3 text-right">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <template v-for="(item, index) in items" :key="item.id">
                                        <!-- Main Row -->
                                        <tr class="text-sm text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">{{ index + 1 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <button
                                                    @click="toggleRow(item.id)"
                                                    class="inline-flex items-center text-indigo-650 dark:text-indigo-400 hover:text-indigo-900 font-semibold"
                                                >
                                                    <svg
                                                        :class="['h-4 w-4 mr-1 transform transition-transform duration-200', expandedRows[item.id] ? 'rotate-90' : '']"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                    Detail
                                                </button>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-650 ring-1 ring-inset ring-gray-500/10 dark:bg-gray-900 dark:text-gray-400">
                                                    {{ item.category }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap font-mono">{{ item.code }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap font-semibold">{{ item.name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right font-medium text-gray-600 dark:text-gray-400">
                                                {{ item.price_range }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right font-bold">
                                                {{ item.total_quantity }} <span class="text-xs font-normal text-gray-500">{{ item.unit_name }}</span>
                                            </td>
                                        </tr>

                                        <!-- Expanded Detail Row -->
                                        <tr v-if="expandedRows[item.id]" class="bg-gray-50/50 dark:bg-gray-900/30">
                                            <td colspan="7" class="px-8 py-4">
                                                <div class="border border-gray-150 dark:border-gray-700 rounded-lg overflow-hidden bg-white dark:bg-gray-850 p-4">
                                                    <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Detail Penempatan & Stok</h4>
                                                    
                                                    <table class="min-w-full divide-y divide-gray-150 dark:divide-gray-700">
                                                        <thead>
                                                            <tr class="text-xxs font-semibold uppercase text-gray-400 dark:text-gray-500">
                                                                <th class="px-4 py-2 text-left w-12">No</th>
                                                                <th class="px-4 py-2 text-left">Lokasi</th>
                                                                <th class="px-4 py-2 text-left">Ekspired</th>
                                                                <th class="px-4 py-2 text-right">Harga</th>
                                                                <th class="px-4 py-2 text-right">Jumlah</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-150 dark:divide-gray-700 text-xs text-gray-700 dark:text-gray-300">
                                                            <tr v-for="(stock, sIndex) in item.stocks" :key="stock.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                                                <td class="px-4 py-2 font-mono">{{ sIndex + 1 }}</td>
                                                                <td class="px-4 py-2 font-medium">{{ stock.location }}</td>
                                                                <td class="px-4 py-2">
                                                                    <span :class="[stock.expiry !== '-' ? 'text-amber-600 dark:text-amber-400 font-mono font-medium' : 'text-gray-400']">
                                                                        {{ stock.expiry }}
                                                                    </span>
                                                                </td>
                                                                <td class="px-4 py-2 text-right font-mono">{{ formatCurrency(stock.price) }}</td>
                                                                <td class="px-4 py-2 text-right font-bold text-indigo-600 dark:text-indigo-400">
                                                                    {{ stock.quantity }}
                                                                </td>
                                                            </tr>
                                                            <tr v-if="item.stocks.length === 0">
                                                                <td colspan="5" class="px-4 py-4 text-center text-gray-400 dark:text-gray-500">Stok kosong di seluruh lokasi.</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr v-if="items.length === 0">
                                        <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">Belum ada data barang.</td>
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
