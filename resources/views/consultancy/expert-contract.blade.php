<x-consultancy-dashboard :application="$application" page="details">
    @include('consultancy.contract.styles')
    @php($prefix = app()->isLocale('en') ? 'en.' : '')
    <div class="mou-tools">
        <a href="{{ route($prefix === 'en.' ? 'expert.contract.show' : 'en.expert.contract.show') }}" lang="{{ $prefix === 'en.' ? 'bn' : 'en' }}">
            {{ $prefix === 'en.' ? 'বাংলা' : 'English' }}
        </a>
        @if ($pdfAvailable)
            <a href="{{ route($prefix.'expert.contract.print') }}">{{ __('expert_mou.print') }}</a>
            <a class="mou-download" href="{{ route($prefix.'expert.contract.pdf') }}">{{ __('expert_mou.download') }}</a>
        @else
            <a class="mou-download" href="{{ route($prefix.'expert.contract.print') }}">{{ __('expert_mou.print_pdf') }}</a>
        @endif
        @if ($application->signature_path)
            <a href="{{ route($prefix.'expert.signature', $application) }}">{{ __('consultancy_engagement.download_signature') }}</a>
        @endif
    </div>
    @include('consultancy.contract.document')
</x-consultancy-dashboard>
