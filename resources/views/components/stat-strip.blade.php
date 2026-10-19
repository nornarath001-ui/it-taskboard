@props(['items'])
<dl class="grid grid-cols-2 divide-ink-200 overflow-hidden rounded-lg border border-ink-200 bg-white sm:grid-cols-4 sm:divide-x">
    @foreach ($items as [$label, $value, $alert])
        <div class="border-b border-ink-200 px-5 py-4 sm:border-b-0 {{ $loop->odd ? 'border-r sm:border-r-0' : '' }}">
            <dt class="text-sm text-ink-500">{{ $label }}</dt>
            <dd @class(['mt-1 font-display text-3xl font-bold', 'text-flare' => $alert && $value > 0])>{{ $value }}</dd>
        </div>
    @endforeach
</dl>
