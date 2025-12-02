<x-filament-panels::page>
    @php
        $viewData = $this->getViewData();
        $evaluation = $viewData['evaluation'];
        $totalResponses = $viewData['total_responses'];
        $statistics = $viewData['statistics'];
    @endphp

    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary-600">{{ $totalResponses }}</div>
                    <div class="text-sm text-gray-500 mt-1">إجمالي الردود</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-success-600">{{ $evaluation->questions->count() }}</div>
                    <div class="text-sm text-gray-500 mt-1">عدد الأسئلة</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-warning-600">
                        {{ $evaluation->product_type === 'course' ? 'دورة' : ($evaluation->product_type === 'bootcamp' ? 'معسكر' : 'ورشة') }}
                    </div>
                    <div class="text-sm text-gray-500 mt-1">نوع المنتج</div>
                </div>
            </x-filament::section>
        </div>

        <!-- Questions Statistics -->
        @foreach($statistics as $stat)
            <x-filament::section>
                <x-slot name="heading">
                    {{ $stat['question']->question_text }}
                </x-slot>

                <x-slot name="description">
                    نوع السؤال: 
                    @switch($stat['question']->question_type)
                        @case('rating')
                            تقييم بالنجوم
                            @break
                        @case('scale')
                            مقياس
                            @break
                        @case('yes_no')
                            نعم/لا
                            @break
                        @case('text')
                            نص حر
                            @break
                        @case('grade')
                            تقدير
                            @break
                    @endswitch
                </x-slot>

                <div class="space-y-4">
                    <div class="text-sm text-gray-600">
                        عدد الردود: <span class="font-semibold">{{ $stat['total_responses'] }}</span>
                    </div>

                    @if(in_array($stat['question']->question_type, ['scale', 'grade', 'yes_no']) && isset($stat['distribution']))
                        <div class="space-y-2">
                            @foreach($stat['distribution'] as $label => $count)
                                <div class="flex items-center gap-4">
                                    <div class="w-32 text-sm font-medium">{{ $label }}</div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-200 rounded-full h-6">
                                                <div 
                                                    class="bg-primary-600 h-6 rounded-full flex items-center justify-end px-2"
                                                    style="width: {{ $stat['total_responses'] > 0 ? ($count / $stat['total_responses'] * 100) : 0 }}%"
                                                >
                                                    <span class="text-xs text-white font-semibold">{{ $count }}</span>
                                                </div>
                                            </div>
                                            <div class="text-sm text-gray-600 w-12 text-right">
                                                {{ $stat['total_responses'] > 0 ? round($count / $stat['total_responses'] * 100, 1) : 0 }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($stat['question']->question_type === 'rating' && isset($stat['average']))
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-bold text-primary-600">{{ $stat['average'] }}</div>
                                <div class="text-sm text-gray-500">المتوسط</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-success-600">{{ $stat['max'] }}</div>
                                <div class="text-sm text-gray-500">الأعلى</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-danger-600">{{ $stat['min'] }}</div>
                                <div class="text-sm text-gray-500">الأدنى</div>
                            </div>
                        </div>
                    @endif

                    @if($stat['question']->question_type === 'text' && isset($stat['text_responses']))
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            @foreach($stat['text_responses'] as $response)
                                <div class="p-3 bg-gray-50 rounded-lg text-sm">
                                    {{ $response }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>