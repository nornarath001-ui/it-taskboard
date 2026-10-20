@php
    $manifestFile = public_path('build/manifest.json');
    $inline = false;

    if (! file_exists(public_path('hot')) && file_exists($manifestFile)) {
        $m = json_decode(file_get_contents($manifestFile), true);
        $cssFile = $m['resources/css/app.css']['file'] ?? null;
        $jsFile = $m['resources/js/app.js']['file'] ?? null;
        // Only inline when the JS is one self-contained file
        $inline = $cssFile && $jsFile && empty($m['resources/js/app.js']['imports']);
    }
@endphp

@if ($inline)
    <style>{!! str_replace('</style', '<\/style', file_get_contents(public_path('build/'.$cssFile))) !!}</style>
    <script type="module">{!! str_replace('</script', '<\/script', file_get_contents(public_path('build/'.$jsFile))) !!}</script>
@else
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@endif
