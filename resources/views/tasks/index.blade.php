<x-app-layout>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-app.navbar />
        <div class="py-4 container-fluid">
            <div class="mt-4 row">
                <div class="col-12">
                    <div class="card">
                        <div class="pb-0 card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h5 class="">{{__('Task Management')}}</h5>
                                    <p class="mb-0 text-sm">

                                    </p>
                                </div>
                                <div class="col-6 text-end">
                                    <a href="{{ route('tasks.create') }}" class="btn btn-dark btn-primary d-none d-lg-inline-block">
                                        <i class="fas fa-plus me-2"></i> {{__("Add Task")}}
                                    </a>
                                    <a href="{{ route('tasks.create') }}" class="btn btn-dark btn-primary d-inline-block d-lg-none">
                                        <i class="fas fa-plus me-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-md-12 px-4">
                                @if (session('success'))
                                    <div class="alert alert-success" role="alert" id="alert">
                                        {{ session('success') }}
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger" role="alert" id="alert">
                                        {{ session('error') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        @include("/tasks/table", compact('categories', 'tasks', 'categoryId'))
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 mb-md-0 mb-4">
                    <div class="card shadow-xs border h-100">
                        <div class="card-header pb-0">
                            <h6 class="font-weight-semibold text-lg mb-0">Evolution</h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="chart mb-2">
                                <canvas id="chart-bars" class="chart-canvas"
                                        height="240" style="display: block; box-sizing: border-box;
                                        height: 240px; width: 474px;" width="474">
                                </canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <script src="/assets/js/plugins/chartjs.min.js"></script>
                <script>
                    var ctx = document.getElementById("chart-bars").getContext("2d");

                    let chart = new Chart(ctx, {
                        type: "bar",
                        data: {
                            labels: {!! json_encode($charts['labels'], JSON_PRETTY_PRINT)!!},
                            datasets: {!! json_encode($charts['datasets'], JSON_PRETTY_PRINT)!!},
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                },
                                tooltip: {
                                    backgroundColor: '#fff',
                                    titleColor: '#1e293b',
                                    bodyColor: '#1e293b',
                                    borderColor: '#e9ecef',
                                    borderWidth: 1,
                                    usePointStyle: true
                                }
                            },
                            interaction: {
                                intersect: false,
                                mode: 'index',
                            },
                            scales: {
                                y: {
                                    stacked: true,
                                    grid: {
                                        drawBorder: false,
                                        display: true,
                                        drawOnChartArea: true,
                                        drawTicks: false,
                                        borderDash: [4, 4],
                                    },
                                    ticks: {
                                        beginAtZero: true,
                                        padding: 10,
                                        font: {
                                            size: 12,
                                            family: "Noto Sans",
                                            style: 'normal',
                                            lineHeight: 2
                                        },
                                        color: "#64748B"
                                    },
                                },
                                x: {
                                    stacked: true,
                                    grid: {
                                        drawBorder: false,
                                        display: false,
                                        drawOnChartArea: false,
                                        drawTicks: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            family: "Noto Sans",
                                            style: 'normal',
                                            lineHeight: 2
                                        },
                                        color: "#64748B"
                                    },
                                },
                            },
                        },
                    });
                </script>
            </div>
        </div>
        <x-app.footer />
    </main>

</x-app-layout>
