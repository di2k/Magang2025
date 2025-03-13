<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title><?php echo $title; ?> | Mytask Log Dashboard</title>

    <!-- CSS files -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler-flags.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler-payments.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler-vendors.min.css" rel="stylesheet" />

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <!-- Custom styles for this template -->
    <style>
        body {
            display: none;
        }

        .card-header-tabs {
            margin-bottom: 0;
        }
    </style>
</head>

<body class="theme-light">
    <div class="page">
        <!-- Sidebar -->
        <aside class="navbar navbar-vertical navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand navbar-brand-autodark">
                    <a href="<?php echo base_url(); ?>">
                        <span class="navbar-brand-text">Mytask Log</span>
                    </a>
                </h1>

                <div class="collapse navbar-collapse" id="sidebar-menu">
                    <ul class="navbar-nav pt-lg-3">
                        <li class="nav-item active">
                            <a class="nav-link" href="<?php echo base_url('mytask_log'); ?>">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="ti ti-dashboard icon"></i>
                                </span>
                                <span class="nav-link-title">Dashboard</span>
                            </a>
                        </li>
                        <!-- Anda dapat menambahkan menu lain di sini -->
                    </ul>
                </div>
            </div>
        </aside>

        <div class="page-wrapper">
            <!-- Page header -->
            <div class="page-header d-print-none">
                <div class="container-fluid">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h2 class="page-title"><?php echo $title; ?></h2>
                            <div class="text-muted mt-1"><?php echo isset($subtitle) ? $subtitle : ''; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page body -->
            <div class="page-body">
                <div class="container-fluid">