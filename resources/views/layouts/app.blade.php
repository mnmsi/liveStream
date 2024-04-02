<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>IOTAIT - Live Stream</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
{{--    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">--}}
{{--    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />--}}

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="{{asset('assets/css/style.css')}}" rel="stylesheet">

    <!-- Jquery datatable Stylesheet -->
    <link href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.min.css" rel="stylesheet">
</head>

<body>
<div class="container-fluid position-relative d-flex p-0">

    @include('components.layout.sidebar')

    <!-- Content Start -->
    <div class="content">

        @include('components.layout.nav')

        <div>
            @yield('content')
        </div>
        <!-- Footer End -->
    </div>
    <!-- Content End -->


    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
</div>

<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.0.2/js/dataTables.min.js"></script>
<script src="{{asset('assets/lib/chart/chart.min.js')}}"></script>
<script src="{{asset('assets/lib/easing/easing.min.js')}}"></script>
<script src="{{asset('assets/lib/waypoints/waypoints.min.js')}}"></script>
<script src="{{asset('assets/lib/owlcarousel/owl.carousel.min.js')}}"></script>
<script src="{{asset('assets/lib/tempusdominus/js/moment.min.js')}}"></script>
<script src="{{asset('assets/lib/tempusdominus/js/moment-timezone.min.js')}}"></script>
<script src="{{asset('assets/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js')}}"></script>

<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>

<!-- Template Javascript -->
<script src="{{asset('assets/js/main.js')}}"></script>

@stack('scripts')
</body>
</html>
