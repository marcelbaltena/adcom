<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                💰 Prijslijst / Service Catalog
            </h2>
            <a href="{{ route('service-templates.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                + Nieuwe Service
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('service-templates.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Zoeken</label>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Zoek op naam..."
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Category Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categorie</label>
                            <select name="category" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Alle categorieën</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Active Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="active" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Alle</option>
                                <option value="yes" {{ request('active') == 'yes' ? 'selected' : '' }}>Actief</option>
                                <option value="no" {{ request('active') == 'no' ? 'selected' : '' }}>Inactief</option>
                            </select>
                        </div>

                        <!-- Submit -->
                        <div class="flex items-end">
                            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded w-full">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Services Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($services as $service)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ $service->name }}
                                        @if($service->is_popular)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Populair
                                            </span>
                                        @endif
                                    </h3>
                                    @if($service->category)
                                        <p class="text-sm text-gray-500">{{ $service->category }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center">
                                    <div class="relative">
                                        <input type="checkbox" 
                                               class="sr-only toggle-active-checkbox" 
                                               data-id="{{ $service->id }}"
                                               {{ $service->is_active ? 'checked' : '' }}>
                                        <div class="w-10 h-6 bg-gray-200 rounded-full shadow-inner toggle-bg {{ $service->is_active ? 'bg-green-500' : '' }}"></div>
                                        <div class="dot absolute w-4 h-4 bg-white rounded-full shadow left-1 top-1 transition {{ $service->is_active ? 'transform translate-x-4' : '' }}"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            @if($service->description)
                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $service->description }}</p>
                            @endif

                            <!-- Price -->
                            <div class="mb-4">
                                <div class="text-2xl font-bold text-blue-600">
                                    €{{ number_format($service->calculateTotalPrice(), 2, ',', '.') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    @if($service->service_type === 'hourly')
                                        {{ $service->estimated_hours }} uur × €{{ number_format($service->hourly_rate, 2, ',', '.') }}/uur
                                    @elseif($service->service_type === 'package')
                                        Pakketprijs
                                    @else
                                        Vaste prijs
                                    @endif
                                </div>
                            </div>

                            <!-- Milestones Preview -->
                            @if($service->milestoneTemplates->count() > 0)
                                <div class="border-t pt-4 mb-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Onderdelen:</h4>
                                    <div class="space-y-1">
                                        @foreach($service->milestoneTemplates->take(3) as $milestone)
                                            <div class="text-sm flex justify-between">
                                                <span class="text-gray-600">• {{ $milestone->title }}</span>
                                                <span class="text-gray-500">€{{ number_format($milestone->calculatePrice(), 2, ',', '.') }}</span>
                                            </div>
                                        @endforeach
                                        @if($service->milestoneTemplates->count() > 3)
                                            <div class="text-sm text-gray-500">
                                                ... en {{ $service->milestoneTemplates->count() - 3 }} meer
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Tags -->
                            @if($service->tags && count($service->tags) > 0)
                                <div class="flex flex-wrap gap-1 mb-4">
                                    @foreach($service->tags as $tag)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <a href="{{ route('service-templates.milestones', $service) }}" 
                                   class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded text-sm text-center transition">
                                    <i class="fas fa-list-check mr-1"></i> Beheren
                                </a>
                                <button onclick="openCloneModal({{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->calculateTotalPrice() }})" 
                                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm transition">
                                    <i class="fas fa-copy mr-1"></i> Naar Project
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                            <i class="fas fa-box-open text-gray-400 text-5xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Geen services gevonden</h3>
                            <p class="text-gray-500 mb-4">Begin met het toevoegen van je eerste service template.</p>
                            <a href="{{ route('service-templates.create') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition">
                                <i class="fas fa-plus mr-2"></i> Nieuwe Service
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $services->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- Include the clone modal partial -->
    @include('service-templates.partials.clone-modal')

    @push('scripts')
    <script>
        // Toggle active status
        document.querySelectorAll('.toggle-active-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const serviceId = this.dataset.id;
                const toggleBg = this.nextElementSibling;
                const dot = toggleBg.nextElementSibling;
                
                fetch(`/service-templates/${serviceId}/toggle-active`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.is_active) {
                            toggleBg.classList.add('bg-green-500');
                            dot.classList.add('transform', 'translate-x-4');
                        } else {
                            toggleBg.classList.remove('bg-green-500');
                            dot.classList.remove('transform', 'translate-x-4');
                        }
                    }
                });
            });
        });

        // Clone modal functions
        let currentServiceId = null;

        function openCloneModal(serviceId, serviceName, defaultPrice) {
            currentServiceId = serviceId;
            document.getElementById('serviceName').textContent = serviceName;
            document.getElementById('defaultPrice').textContent = defaultPrice.toFixed(2).replace('.', ',');
            document.getElementById('cloneForm').action = `/service-templates/${serviceId}/clone-to-project`;
            document.getElementById('cloneModal').classList.remove('hidden');
        }

        function closeCloneModal() {
            document.getElementById('cloneModal').classList.add('hidden');
            document.getElementById('cloneForm').reset();
        }

        // Toggle discount section
        document.getElementById('applyDiscount').addEventListener('change', function() {
            const discountSection = document.getElementById('discountSection');
            if (this.checked) {
                discountSection.classList.remove('hidden');
            } else {
                discountSection.classList.add('hidden');
            }
        });

        // Close modal on background click
        document.getElementById('cloneModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCloneModal();
            }
        });
    </script>
    @endpush
</x-app-layout>