<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="icon" href="{{ url('aset/rupaia_logo.png') }}">
    <title>Admin Dashboard - Rupaia</title>

    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="{{ asset('sbadmin/css/styles.css') }}" rel="stylesheet" />

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    <link rel="stylesheet"
        href="https://cdn-uicons.flaticon.com/3.0.0/uicons-regular-rounded/css/uicons-regular-rounded.css">

    <style>
        main {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fa;
        }

        .admin-page-wrapper {
            padding-top: 1.5rem;
            padding-bottom: 2rem;
        }

        .admin-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 0.125rem 0.75rem rgba(0, 0, 0, 0.06);
        }

        .admin-table-wrapper {
            background: #fff;
            border-radius: 1rem;
            padding: 1rem;
            box-shadow: 0 0.125rem 0.75rem rgba(0, 0, 0, 0.06);
        }

        .admin-table-wrapper table {
            margin-bottom: 0;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        @media (max-width: 768px) {
            .admin-page-title {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 0.75rem;
            }

            .admin-table-wrapper {
                padding: 0.75rem;
            }

            .table {
                white-space: nowrap;
            }
        }
    </style>
</head>

<body class="sb-nav-fixed">
    @include('admin.partials.navbar')

    <div id="layoutSidenav">
        @include('admin.partials.sidebar')

        <div id="layoutSidenav_content">
            <main>
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script src="{{ asset('sbadmin/js/scripts.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
        crossorigin="anonymous"></script>
</body>

</html>
