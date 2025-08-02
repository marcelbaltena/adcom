<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Project Sjablonen
            </h2>
            <a href="{{ route('project-templates.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-plus mr-1"></i> Nieuw Sjabloon
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($templates->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($templates as $template)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                    <div class="p-6">
                                        <div class="flex justify-between items-start mb-4">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $template->name }}
                                            </h3>
                                            @if($template->usage_count > 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ $template->usage_count }}x gebruikt
                                                </span>
                                            @endif
                                        </div>
                                        
                                        @if($template->description)
                                            <p class="text-gray-600 text-sm mb-4">
                                                {{ Str::limit($template->description, 100) }}
                                            </p>
                                        @endif
                                        
                                        <div class="mb-4">
                                            <div class="flex items-center text-sm text-gray-500">
                                                <i class="fas fa-flag-checkered mr-2"></i>
                                                {{-- AANPASSING: gebruik 'milestones' in plaats van 'milestoneTemplates' --}}
                                                <span>{{ $template->milestones->count() }} milestones</span>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500 mt-1">
                                                <i class="fas fa-clock mr-2"></i>
                                                <span>{{ $template->total_days }} dagen doorlooptijd</span>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-500 mt-1">
                                                <i class="fas fa-hourglass-half mr-2"></i>
                                                <span>{{ number_format($template->getTotalEstimatedHours(), 1) }} uur geschat</span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex space-x-2">
                                            <a href="{{ route('project-templates.milestones', $template) }}" 
                                               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center font-medium py-2 px-4 rounded text-sm">
                                                <i class="fas fa-eye mr-1"></i> Bekijken
                                            </a>
                                            <a href="{{ route('project-templates.edit', $template) }}" 
                                               class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded text-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($template->usage_count == 0)
                                                <form action="{{ route('project-templates.destroy', $template) }}" 
                                                      method="POST" 
                                                      class="inline"
                                                      onsubmit="return confirm('Weet je zeker dat je dit sjabloon wilt verwijderen?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded text-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-file-alt text-gray-400 text-6xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nog geen sjablonen</h3>
                            <p class="text-gray-500 mb-6">Begin met het maken van je eerste project sjabloon.</p>
                            <a href="{{ route('project-templates.create') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-plus mr-1"></i> Eerste sjabloon maken
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>