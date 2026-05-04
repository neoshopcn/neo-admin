@extends('admin.layouts.content')

@section('title', '菜单管理')

@push('scripts')
    <script>
        window.__MENUS_ADMIN__ = {
            treeUrl: @json($treeUrl),
            permissionsUrl: @json($permissionsUrl),
        };
    </script>
    <script src="{{ asset('js/menus-admin.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            neoAxiosSetup(axios);
            mountMenusAdmin('#app', window.__MENUS_ADMIN__);
        });
    </script>
@endpush
