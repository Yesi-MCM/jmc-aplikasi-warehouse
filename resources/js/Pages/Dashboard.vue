<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    metrics: Object,
    recent_incoming: Array,
    recent_outgoing: Array,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Dashboard Gudang
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Metrics -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <!-- Metric 1 -->
                    <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 border-l-4 border-indigo-500">
                        <div class="flex items-center">
                            <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-md mr-4 text-indigo-600 dark:text-indigo-300">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Jenis Barang</p>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ metrics.total_items }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Metric 2 -->
                    <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 border-l-4 border-emerald-500">
                        <div class="flex items-center">
                            <div class="p-3 bg-emerald-100 dark:bg-emerald-900 rounded-md mr-4 text-emerald-600 dark:text-emerald-300">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Stok Fisik</p>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ metrics.total_stock_qty }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Metric 3 -->
                    <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 border-l-4 border-blue-500">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-md mr-4 text-blue-600 dark:text-blue-300">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Nilai Barang</p>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(metrics.total_stock_value) }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Metric 4 -->
                    <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 border-l-4 border-amber-500">
                        <div class="flex items-center">
                            <div class="p-3 bg-amber-100 dark:bg-amber-900 rounded-md mr-4 text-amber-600 dark:text-amber-300">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Stock Opname Aktif</p>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ metrics.active_opnames }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Recent Incoming -->
                    <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-6 py-4 flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-950 dark:text-white">5 Barang Masuk Terakhir</h3>
                            <Link :href="route('incoming-goods.create')" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                                + Catat Barang Masuk
                            </Link>
                        </div>
                        <div class="p-6">
                            <div v-if="recent_incoming.length === 0" class="text-center py-6 text-gray-500 dark:text-gray-400">
                                Belum ada data barang masuk.
                            </div>
                            <div v-else class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Tanggal</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">No. Surat</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Vendor</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Pencatat</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="inc in recent_incoming" :key="inc.id" class="text-sm text-gray-800 dark:text-gray-200">
                                            <td class="px-4 py-3 whitespace-nowrap">{{ formatDate(inc.created_at) }}</td>
                                            <td class="px-4 py-3 font-mono font-medium">{{ inc.invoice_number }}</td>
                                            <td class="px-4 py-3">{{ inc.vendor_name }}</td>
                                            <td class="px-4 py-3">{{ inc.creator.name }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Outgoing -->
                    <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-6 py-4 flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-950 dark:text-white">5 Barang Keluar Terakhir</h3>
                            <Link :href="route('outgoing-goods.create')" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                                + Catat Barang Keluar
                            </Link>
                        </div>
                        <div class="p-6">
                            <div v-if="recent_outgoing.length === 0" class="text-center py-6 text-gray-500 dark:text-gray-400">
                                Belum ada data barang keluar.
                            </div>
                            <div v-else class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Tanggal</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Penerima</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Unit</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Pencatat</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="out in recent_outgoing" :key="out.id" class="text-sm text-gray-800 dark:text-gray-200">
                                            <td class="px-4 py-3 whitespace-nowrap">{{ formatDate(out.created_at) }}</td>
                                            <td class="px-4 py-3">{{ out.receiver_name }}</td>
                                            <td class="px-4 py-3">{{ out.unit.name }}</td>
                                            <td class="px-4 py-3">{{ out.creator.name }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
