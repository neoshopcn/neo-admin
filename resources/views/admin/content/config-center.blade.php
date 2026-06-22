@extends('admin.layouts.content')

@section('title', $title)

@push('scripts')
    <script>window.__NEO_CONFIG__ = @json($neo);</script>
    <script src="{{ asset('js/config-center.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            neoAxiosSetup(axios);
            mountConfigCenter('#app', window.__NEO_CONFIG__);
        });
    </script>
@endpush
