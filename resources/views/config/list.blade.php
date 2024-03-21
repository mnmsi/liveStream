@extends('layouts.app')
@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-12">
                <div class="bg-secondary rounded h-100 p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-4">Streaming</h6>
                        <a href="{{ route('config.create') }}" type="button" class="btn btn-primary m-2">+ Add New</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="stream-table">
                            <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Info</th>
                                <th scope="col">Active Users</th>
                                <th scope="col">Incoming Bandwidth</th>
                                <th scope="col">Outgoing Bandwidth</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function () {
            streams();

            // Periodically reload every 5 seconds
            setInterval(function () {
                $('#stream-table').DataTable().draw();
            }, 5000);
        });

        function streams() {
            $('#stream-table').DataTable({
                processing: false,
                serverSide: true,
                ordering: false,
                ajax: '{{ route('config.list') }}',
                columns: [
                    {
                        data: 'id', name: 'id', render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {data: 'info', name: 'info'},
                    {data: 'active_users', name: 'active_users', className: 'text-center'},
                    {data: 'incoming_bandwidth', name: 'incoming_bandwidth'},
                    {data: 'outgoing_bandwidth', name: 'outgoing_bandwidth'},
                    {data: 'status', name: 'status', className: 'text-center'},
                    {data: 'action', name: 'action', className: 'text-center'},
                ]
            });
        }
    </script>
@endpush
