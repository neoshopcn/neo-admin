@extends('admin.layouts.content')

@section('title', '小程序配置')

@push('scripts')
    <script>window.__NEO_CONFIG__ = @json($neo);</script>
    <script src="{{ asset('js/neo-upload.js') }}"></script>
    <script src="{{ asset('js/neo-table.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            neoAxiosSetup(axios);
            mountNeoTable('#app', window.__NEO_CONFIG__);
        });
    </script>
@endpush
