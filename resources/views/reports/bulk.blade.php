<x-layouts.app title="Bulk Operations">
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Bulk Operations</h1><p class="text-gray-500 mt-1">Import and export data in bulk</p></div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Export</h3>
            <div class="space-y-3">
                <a href="#" class="btn-secondary w-full justify-center">Export Products</a>
                <a href="#" class="btn-secondary w-full justify-center">Export Clients</a>
                <a href="#" class="btn-secondary w-full justify-center">Export Orders</a>
            </div>
        </div>
        <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Import</h3>
            <div class="space-y-3">
                <div class="p-8 border-2 border-dashed border-gray-300 rounded-xl text-center text-gray-400">
                    <p>Drag & drop CSV files here or click to upload</p>
                    <p class="text-xs mt-2">Supports Products and Clients import</p>
                </div>
            </div>
        </div>
    </div>
</div>
</x-layouts.app>
