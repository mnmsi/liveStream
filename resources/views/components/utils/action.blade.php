<div class="d-flex">
    @isset($view)
        <button class="border-0 btn-transition btn btn-outline-danger" onclick="window.location.href='{{$view}}'">
            <i class="fa fa-eye"></i>
        </button>
    @endisset
    @isset($edit)
        <button class="border-0 btn-transition btn btn-outline-info" onclick="window.location.href='{{$edit}}'">
            <i class="fa fa-pencil-alt"></i>
        </button>
    @endisset
    @isset($delete)
        <button class="border-0 btn-transition btn btn-outline-danger" onclick="fnDelete('{{$delete}}', '{{$rowId}}')">
            <i class="fa fa-trash-alt"></i>
        </button>
    @endisset
</div>

{{--
<button class="border-0 btn-transition btn btn-outline-info" onclick="window.location.href='test'">
        <i class="fa fa-eye"></i>
    </button>
    <button class="border-0 btn-transition btn btn-outline-primary" onclick="window.location.href='test'">
        <i class="fa fa-pencil-alt"></i>
    </button>
    <button class="border-0 btn-transition btn btn-outline-danger" onclick="fnDelete('test', 'test')">
        <i class="fa fa-trash-alt"></i>
    </button>
--}}
