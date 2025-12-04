@php
    $settings = $this->getRecord() ?? new \App\Models\LinkTreeSetting();
    $links = \App\Models\LinkTreeLink::active()->ordered()->take(3)->get();
@endphp

<div class="rounded-lg border border-gray-200 dark:border-gray-700 p-6">
    <div class="text-center mb-4">
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">معاينة تقريبية</h3>
    </div>
    
    <div 
        class="max-w-sm mx-auto rounded-lg p-8 min-h-[400px]"
        style="background-color: {{ $settings->background_color ?? '#ffffff' }}; font-family: {{ $settings->font_family ?? 'Arial' }};"
    >
        @if($settings->page_title)
            <h1 class="text-2xl font-bold text-center mb-2" style="color: {{ $settings->button_color ?? '#000000' }};">
                {{ $settings->page_title }}
            </h1>
        @endif
        
        @if($settings->page_description)
            <p class="text-center mb-6 text-gray-600">
                {{ $settings->page_description }}
            </p>
        @endif
        
        <div class="space-y-3">
            @forelse($links as $link)
                <a 
                    href="#"
                    class="block w-full text-center py-3 px-4 rounded-lg transition-transform hover:scale-105"
                    style="background-color: {{ $settings->button_color ?? '#000000' }}; color: {{ $settings->text_color ?? '#ffffff' }};"
                    onclick="event.preventDefault();"
                >
                    {{ $link->name }}
                </a>
            @empty
                <div class="text-center text-gray-500 py-8">
                    لا توجد روابط للمعاينة
                </div>
            @endforelse
        </div>
    </div>
    
    <p class="text-xs text-gray-500 text-center mt-4">
        * هذه معاينة تقريبية، قد يختلف المظهر الفعلي قليلاً
    </p>
</div>

{{-- resources/views/filament/modals/copy-url.blade.php --}}
<div class="p-4">
    <div class="flex items-center justify-between p-3 bg-gray-100 dark:bg-gray-800 rounded-lg">
        <code class="text-sm">{{ $url }}</code>
        <button 
            onclick="navigator.clipboard.writeText('{{ $url }}'); alert('تم نسخ الرابط!');"
            class="ml-2 px-3 py-1 bg-primary-600 text-white rounded text-sm hover:bg-primary-700"
        >
            نسخ
        </button>
    </div>
</div>

{{-- resources/views/link-tree/show.blade.php (Public facing page) --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings->page_title ?? 'روابطنا' }}</title>
    <meta name="description" content="{{ $settings->page_description }}">
    
    @if($settings->font_family === 'Cairo' || $settings->font_family === 'Tajawal' || $settings->font_family === 'IBM Plex Sans Arabic')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family={{ $settings->font_family }}:wght@400;600;700&display=swap" rel="stylesheet">
    @endif
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: '{{ $settings->font_family }}', sans-serif;
            background-color: {{ $settings->background_color }};
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 680px;
            width: 100%;
            padding: 40px 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .title {
            font-size: 32px;
            font-weight: 700;
            color: {{ $settings->button_color }};
            margin-bottom: 12px;
        }
        
        .description {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
        }
        
        .links {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .link-button {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px 24px;
            background-color: {{ $settings->button_color }};
            color: {{ $settings->text_color }};
            text-decoration: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .link-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .link-button:active {
            transform: translateY(0);
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #999;
            font-size: 14px;
        }
        
        @media (max-width: 640px) {
            .title {
                font-size: 24px;
            }
            
            .description {
                font-size: 14px;
            }
            
            .link-button {
                font-size: 14px;
                padding: 14px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @if($settings->page_title || $settings->page_description)
            <div class="header">
                @if($settings->page_title)
                    <h1 class="title">{{ $settings->page_title }}</h1>
                @endif
                
                @if($settings->page_description)
                    <p class="description">{{ $settings->page_description }}</p>
                @endif
            </div>
        @endif
        
        <div class="links">
            @foreach($links as $link)
                <a 
                    href="{{ $link->url }}" 
                    class="link-button"
                    target="_blank"
                    rel="noopener noreferrer"
                    onclick="trackClick({{ $link->id }})"
                >
                    {{ $link->name }}
                </a>
            @endforeach
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} جميع الحقوق محفوظة</p>
        </div>
    </div>
    
    <script>
        function trackClick(linkId) {
            fetch(`/api/link-tree/track/${linkId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).catch(err => console.error('Error tracking click:', err));
        }
    </script>
</body>
</html>