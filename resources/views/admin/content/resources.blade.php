@extends('admin.layouts.content')

@section('title', '资源管理')

@push('scripts')
    <script>window.__NEO_CONFIG__ = @json($neo);</script>
    <script src="{{ asset('js/neo-upload.js') }}"></script>
    <script src="{{ asset('js/neo-table.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            neoAxiosSetup(axios);
            mountNeoTable('#app', window.__NEO_CONFIG__, {
                skipCreateApi: true,
                createPathField: 'storage_path',
                createPathHint: '请先选择场景并上传文件',
            });
        });
    </script>
@endpush
