<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Sjabloon toepassen op: {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            {{-- Waarschuwing als project al milestones heeft --}}
            @if($project->milestones->count() > 0)
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                Let op: Dit project heeft al {{ $project->milestones->count() }} milestone(s)
                            </h3>
                            <p class="mt-2 text-sm text-yellow-700">
                                Het toepassen van een sjabloon zal nieuwe milestones toevoegen aan de bestaande milestones.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('projects.apply-template.store', $project) }}">
                        @csrf
                        
                        <div class="mb-6">
                            <label for="project_template_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Selecteer sjabloon <span class="text-red-500">*</span>
                            </label>
                            <select name="project_template_id" 
                                    id="project_template_id" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                    onchange="showTemplateDetails(this.value)">
                                <option value="">-- Kies een sjabloon --</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" 
                                            data-milestones="{{ $template->milestones->count() }}"
                                            data-days="{{ $template->total_days }}"
                                            data-hours="{{ $template->getTotalEstimatedHours() }}"
                                            data-description="{{ $template->description }}">
                                        {{ $template->name }}
                                        @if($template->category)
                                            ({{ $template->category }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('project_template_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Template Details (hidden by default) -->
                        <div id="templateDetails" class="mb-6 p-4 bg-gray-50 rounded-md hidden">
                            <h4 class="font-medium text-gray-900 mb-2">Sjabloon details:</h4>
                            <div class="grid grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Milestones:</span>
                                    <span id="detailMilestones" class="font-medium ml-1">-</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Doorlooptijd:</span>
                                    <span id="detailDays" class="font-medium ml-1">-</span> dagen
                                </div>
                                <div>
                                    <span class="text-gray-500">Geschatte uren:</span>
                                    <span id="detailHours" class="font-medium ml-1">-</span> uur
                                </div>
                            </div>
                            <div id="detailDescription" class="mt-2 text-sm text-gray-600"></div>
                        </div>
                        
                        <div class="mb-6">
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Startdatum planning <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="start_date" 
                                   id="start_date" 
                                   value="{{ old('start_date', $project->start_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   required
                                   onchange="calculateEndDate()">
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">
                                Alle milestones worden gepland vanaf deze datum volgens het sjabloon.
                            </p>
                        </div>
                        
                        <div id="endDateInfo" class="mb-6 p-4 bg-blue-50 rounded-md hidden">
                            <p class="text-sm text-blue-800">
                                <i class="fas fa-info-circle mr-1"></i>
                                Geschatte einddatum: <span id="calculatedEndDate" class="font-medium">-</span>
                            </p>
                        </div>
                        
                        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                            <h4 class="font-medium text-yellow-800 mb-2">Let op:</h4>
                            <ul class="list-disc list-inside text-sm text-yellow-700 space-y-1">
                                <li>Alle prijzen en budgetten worden op €0,00 gezet</li>
                                <li>Je kunt na het toepassen alle gegevens nog aanpassen</li>
                                <li>Bestaande milestones in het project blijven behouden</li>
                            </ul>
                        </div>
                        
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('projects.milestones', $project) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded">
                                Annuleren
                            </a>
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                                Sjabloon toepassen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTemplateDetails(templateId) {
            const select = document.getElementById('project_template_id');
            const option = select.querySelector(`option[value="${templateId}"]`);
            const detailsDiv = document.getElementById('templateDetails');
            
            if (templateId && option) {
                document.getElementById('detailMilestones').textContent = option.dataset.milestones;
                document.getElementById('detailDays').textContent = option.dataset.days;
                document.getElementById('detailHours').textContent = parseFloat(option.dataset.hours).toFixed(1);
                document.getElementById('detailDescription').textContent = option.dataset.description || '';
                detailsDiv.classList.remove('hidden');
                
                calculateEndDate();
            } else {
                detailsDiv.classList.add('hidden');
                document.getElementById('endDateInfo').classList.add('hidden');
            }
        }
        
        function calculateEndDate() {
            const templateSelect = document.getElementById('project_template_id');
            const startDateInput = document.getElementById('start_date');
            const option = templateSelect.selectedOptions[0];
            
            if (templateSelect.value && startDateInput.value && option) {
                const days = parseInt(option.dataset.days) || 0;
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + days);
                
                const formattedDate = endDate.toLocaleDateString('nl-NL', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                
                document.getElementById('calculatedEndDate').textContent = formattedDate;
                document.getElementById('endDateInfo').classList.remove('hidden');
            }
        }
    </script>
</x-app-layout>