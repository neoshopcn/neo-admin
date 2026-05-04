@extends('admin.layouts.content')

@section('title', '角色管理')

@push('scripts')
    <script>window.__NEO_CONFIG__ = @json($neo);</script>
    <script src="{{ asset('js/neo-table.js') }}"></script>
    <script src="{{ asset('js/role-assign-bridge.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            neoAxiosSetup(axios);
            const host = document.createElement('div');
            host.id = 'assignBridge';
            document.body.appendChild(host);
            const bridge = mountRoleAssignBridge({
                el: '#assignBridge',
                treeUrl: @json($menuTreeUrl),
                roleBaseUrl: @json($assignUrl),
            });
            mountNeoTable('#app', window.__NEO_CONFIG__, {
                onExtra: function (action, row) {
                    if (action === 'assign') {
                        bridge.open(row);
                    }
                },
            });
        });
    </script>
@endpush
