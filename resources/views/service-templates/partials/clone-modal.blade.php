<!-- Clone to Project Modal Partial -->
<div id="cloneModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <form id="cloneForm" method="POST" action="">
            @csrf
            <h3 class="text-lg font-bold text-gray-900 mb-4">Service toevoegen aan project</h3>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">
                    Je staat op het punt om <span id="serviceName" class="font-semibold"></span> toe te voegen aan een project.
                </p>
            </div>

            <!-- Project Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Selecteer project</label>
                
                @php
                    $projectCount = 0;
                    try {
                        $projectsQuery = DB::select('SELECT id, name FROM projects ORDER BY created_at DESC');
                        $projectCount = count($projectsQuery);
                    } catch (\Exception $e) {
                        $projectsQuery = [];
                    }
                @endphp
                
                @if($projectCount > 0)
                    <select name="project_id" id="projectSelect" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="">Kies een project... ({{ $projectCount }} beschikbaar)</option>
                        @foreach($projectsQuery as $proj)
                            <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                        @endforeach
                    </select>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                        <p class="text-sm text-yellow-800">
                            Er zijn geen projecten gevonden in de database.
                        </p>
                        <a href="/projects/create" class="mt-2 inline-block bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                            Maak eerst een project aan
                        </a>
                    </div>
                    <input type="hidden" name="project_id" value="">
                @endif
            </div>

            <!-- Custom Price -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Prijs aanpassen (optioneel)</label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">€</span>
                    <input type="number" 
                           name="custom_price" 
                           id="customPrice"
                           step="0.01" 
                           min="0"
                           class="pl-8 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Laat leeg voor standaard prijs">
                </div>
                <p class="text-xs text-gray-500 mt-1">Standaard: €<span id="defaultPrice"></span></p>
            </div>

            <!-- Discount -->
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="apply_discount" id="applyDiscount" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Korting toepassen</span>
                </label>
            </div>

            <div id="discountSection" class="mb-4 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kortingspercentage</label>
                <div class="relative">
                    <input type="number" 
                           name="discount_percentage" 
                           step="1" 
                           min="0" 
                           max="100"
                           class="pr-8 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="0">
                    <span class="absolute right-3 top-2 text-gray-500">%</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeCloneModal()" 
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition">
                    Annuleren
                </button>
                @if($projectCount > 0)
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition">
                        Toevoegen aan project
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>