<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $serviceTemplate->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Service Template Preview</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('service-templates.milestones', $serviceTemplate) }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-cog mr-1"></i> Beheren
                </a>
                <button onclick="openCloneModal()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-copy mr-1"></i> Naar Project
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Service Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Service Details</h3>
                            
                            @if($serviceTemplate->description)
                                <p class="text-gray-700 mb-4">{{ $serviceTemplate->description }}</p>
                            @endif

                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Categorie:</dt>
                                    <dd class="text-sm text-gray-900">{{ $serviceTemplate->category ?: 'Geen categorie' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Type:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ \App\Models\ServiceTemplate::getServiceTypeOptions()[$serviceTemplate->service_type] }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Status:</dt>
                                    <dd class="text-sm">
                                        @if($serviceTemplate->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Actief
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Inactief
                                            </span>
                                        @endif
                                        @if($serviceTemplate->is_popular)
                                            <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Populair
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            @if($serviceTemplate->tags && count($serviceTemplate->tags) > 0)
                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Tags</h4>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($serviceTemplate->tags as $tag)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Prijs Overzicht</h3>
                            
                            <div class="bg-blue-50 rounded-lg p-4 mb-4">
                                <div class="text-center">
                                    <p class="text-sm text-blue-600 font-medium">Totaalprijs</p>
                                    <p class="text-3xl font-bold text-blue-700 mt-1">
                                        €{{ number_format($serviceTemplate->calculateTotalPrice(), 2, ',', '.') }}
                                    </p>
                                    @if($serviceTemplate->service_type === 'hourly')
                                        <p class="text-sm text-blue-600 mt-1">
                                            {{ $serviceTemplate->estimated_hours }}u × €{{ number_format($serviceTemplate->hourly_rate, 2, ',', '.') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Geschatte uren:</dt>
                                    <dd class="text-sm text-gray-900">{{ number_format($serviceTemplate->getTotalEstimatedHours(), 2, ',', '.') }} uur</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Aantal milestones:</dt>
                                    <dd class="text-sm text-gray-900">{{ $serviceTemplate->milestoneTemplates->count() }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Totaal taken:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $serviceTemplate->milestoneTemplates->sum(fn($m) => $m->taskTemplates->count()) }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Totaal subtaken:</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $serviceTemplate->milestoneTemplates->sum(fn($m) => 
                                            $m->taskTemplates->sum(fn($t) => $t->subtaskTemplates->count())
                                        ) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Milestones Breakdown -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Milestones & Taken</h3>

                @foreach($serviceTemplate->milestoneTemplates as $milestone)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-6 py-3 border-b">
                            <div class="flex justify-between items-center">
                                <h4 class="text-lg font-medium text-gray-900">
                                    {{ $milestone->title }}
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $milestone->fee_type === 'in_fee' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $milestone->fee_type === 'in_fee' ? 'In Fee' : 'Extended' }}
                                    </span>
                                </h4>
                                <p class="text-lg font-semibold text-gray-900">
                                    €{{ number_format($milestone->calculatePrice(), 2, ',', '.') }}
                                </p>
                            </div>
                            @if($milestone->description)
                                <p class="mt-1 text-sm text-gray-600">{{ $milestone->description }}</p>
                            @endif
                        </div>

                        <div class="p-6">
                            @if($milestone->deliverables && count($milestone->deliverables) > 0)
                                <div class="mb-4">
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Deliverables:</h5>
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach($milestone->deliverables as $deliverable)
                                            <li class="text-sm text-gray-600">{{ $deliverable }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($milestone->taskTemplates->count() > 0)
                                <div>
                                    <h5 class="text-sm font-medium text-gray-700 mb-2">Taken:</h5>
                                    <div class="space-y-2">
                                        @foreach($milestone->taskTemplates as $task)
                                            <div class="bg-gray-50 rounded-lg p-3">
                                                <div class="flex justify-between items-start">
                                                    <div class="flex-1">
                                                        <h6 class="font-medium text-gray-900">
                                                            {{ $task->title }}
                                                            @if($task->fee_type === 'extended')
                                                                <span class="ml-1 text-xs text-purple-600">(Extended)</span>
                                                            @endif
                                                        </h6>
                                                        @if($task->description)
                                                            <p class="text-sm text-gray-600 mt-1">{{ $task->description }}</p>
                                                        @endif
                                                        
                                                        @if($task->subtaskTemplates->count() > 0)
                                                            <div class="mt-2">
                                                                <p class="text-xs font-medium text-gray-500 mb-1">Subtaken:</p>
                                                                <div class="flex flex-wrap gap-1">
                                                                    @foreach($task->subtaskTemplates as $subtask)
                                                                        <span class="inline-block bg-white rounded px-2 py-1 text-xs text-gray-600 border border-gray-200">
                                                                            {{ $subtask->title }}
                                                                            @if($subtask->estimated_minutes > 0)
                                                                                <span class="text-gray-400">({{ $subtask->estimated_minutes }}m)</span>
                                                                            @endif
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="text-right ml-4">
                                                        <p class="font-medium text-gray-900">
                                                            €{{ number_format($task->calculatePrice(), 2, ',', '.') }}
                                                        </p>
                                                        <p class="text-xs text-gray-500">
                                                            @if($task->pricing_type === 'hourly_rate')
                                                                {{ $task->estimated_hours }}u × €{{ number_format($task->hourly_rate, 2, ',', '.') }}
                                                            @else
                                                                Vaste prijs
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Clone Modal (zelfde als eerder) -->
    <div id="cloneModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <!-- Zelfde inhoud als in milestones.blade.php -->
    </div>

    @push('scripts')
    <script>
        // Clone modal functions (zelfde als eerder)
        function openCloneModal() {
            document.getElementById('cloneModal').classList.remove('hidden');
            loadProjects();
        }

        function closeCloneModal() {
            document.getElementById('cloneModal').classList.add('hidden');
            document.getElementById('cloneForm').reset();
        }

        // Etc... (rest van de JavaScript)
    </script>
    @endpush
</x-app-layout>