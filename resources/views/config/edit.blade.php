@extends('layouts.app')
@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-12 col-sm-12 col-xl-6">
                <div class="bg-secondary rounded h-100 p-4">
                    <h6 class="mb-4">Create new stream</h6>
                    <form action="{{ route('config.update', $config->id) }}" method="post">
                        @csrf

                        <div class="mb-3">
                            <label for="givenName" class="form-label">Name</label>
                            <input type="text" name="given_name" class="form-control" id="givenName"
                                   placeholder="Enter stream name"
                                   aria-describedby="emailHelp"
                                   value="{{$config->given_name}}">
                            @include('components.utils.form_field_alert', ['name' => 'given_name'])
                        </div>

                        <div class="mb-3">
                            <label for="sourceUrl" class="form-label">Source Url <small>(if available)</small></label>
                            <input type="text" name="source_url" class="form-control" id="sourceUrl" placeholder="Enter source url" value="{{$config->source_url}}">
                            @include('components.utils.form_field_alert', ['name' => 'source_url'])
                        </div>

                        <div>
                            <button type="submit" class="btn btn-primary">Create</button>
                            <a href="javascript:history.back()" type="button" class="btn btn-outline-light m-2">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
