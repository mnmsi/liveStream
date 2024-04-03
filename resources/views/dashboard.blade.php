@extends('layouts.app')
@section('content')
    <!-- Sale & Revenue Start -->
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-sm-6 col-xl-3">
                <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-line fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Today Users</p>
                        <h6 class="mb-0">{{$totalUsers}}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-bar fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Total Countries</p>
                        <h6 class="mb-0">{{$totalCountries}}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Sale & Revenue End -->

    <!-- Chart Start -->
    @if($totalCountries)
        <div class="container-fluid pt-4 px-4 mb-4">
            <div class="row g-4">
                <div class="col-sm-12 col-xl-6">
                    <div class="bg-secondary rounded h-100 p-4">
                        <h6 class="mb-4">Pie Chart</h6>
                        <canvas id="pie-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!-- Chart End -->
@endsection
@push('scripts')
    <script>
        $(document).ready(function () {
            // Pie Chart
            var ctx5 = $("#pie-chart").get(0).getContext("2d");

            let countries = @json($countries);
            let data = @json($countriesUsers);

            if (countries) {
                new Chart(ctx5, {
                    type: "pie",
                    data: {
                        labels: countries,
                        datasets: [{
                            backgroundColor: generateRandomColors(countries.length),
                            data: data
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });
            }
        })

        function generateRandomColors(numColors) {
            var colors = [];
            for (var i = 0; i < numColors; i++) {
                var color = 'rgba(' +
                    Math.floor(Math.random() * 256) + ',' +
                    Math.floor(Math.random() * 256) + ',' +
                    Math.floor(Math.random() * 256) + ',' +
                    '0.7)';
                colors.push(color);
            }
            console.log(colors)
            return colors;
        }
    </script>
@endpush
