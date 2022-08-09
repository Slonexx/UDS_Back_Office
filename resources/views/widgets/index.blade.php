<!doctype html>
<html lang="en">
@include('head')

<body>

<div>

    @yield('counterparty')

</div>



<style>
    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        font-size: 12pt;
    }

    .btn-new:hover{
        border-color: white !important
    }

    .btn-new:focus{
        border-color: white !important;
        color: orange !important;
    }

    .text-orange{
        color: orange;
    }

</style>

</body>
</html>

