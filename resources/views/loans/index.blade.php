<x-app-layout>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-app.navbar />
        <div class="container-fluid py-4">
            <div class="mt-4 row">
                <div class="col-12">
                    <div class="card">
                        <div class="pb-0 card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h5 class="">{{__('Loan Management')}}</h5>
                                    <p class="mb-0 text-sm">
                                        <b>{{__("Already paid")}}: {{currency($totalPaid)}} / {{currency($total)}}</b>
                                    </p>
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
                        <div class="border-bottom py-3 px-3 d-sm-flex align-items-center">
                            <div class="input-group w-sm-25 ms-auto py-2 py-lg-0">
                                <span class="input-group-text text-body">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z">
                                </path>
                                </svg>
                                </span>
                                <input type="text" id="datatable-search" class="form-control" placeholder="{{__("Search")}}">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table text-secondary text-center" id="datatable">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="align-middle">
                                            {{__("Name")}}
                                        </th>
                                        <th>
                                            {{__("Remaining")}}
                                        </th>
                                        <th>
                                            {{__("Amount")}}
                                        </th>
                                        <th>
                                            {{__("Active")}}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($loans as $loan)
                                        <tr>
                                            <td class="align-middle bg-transparent border-bottom">
                                                <div class="d-flex">
                                                    <a href="/loans/{{$loan->id}}/edit">
                                                        <i class="fa {{$loan->icon}}"></i>
                                                        &nbsp;&nbsp;{{__($loan->name)}}
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="align-middle bg-transparent border-bottom">
                                                @if ($loan->amount_now != null)
                                                    {{$loan->amount_now}}
                                                @else
                                                    0
                                                @endif
                                            </td>
                                            <td class="align-middle bg-transparent border-bottom">
                                                @if ($loan->amount != null)
                                                    {{$loan->amount}}
                                                @else
                                                    0
                                                @endif
                                            </td>
                                            <td class="align-middle bg-transparent border-bottom">
                                                <span class="badge badge-sm border @if (!$loan->active)
                                                    border-secondary text-secondary bg-secondary @else border-success text-success bg-success @endif">
                                                    <a href="/loans/{{$loan->id}}/edit">
                                                        @if ($loan->active) ON @else OFF @endif
                                                    </a>
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>{{__("Total page")}}
                                            <br/>{{__("Total full")}}
                                        </th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <br/>

                        <div class="row mt-4">
                            <div class="col-lg-12 col-md-12 mb-md-0 mb-4">
                                <div class="card shadow-xs border h-100">
                                    <div class="card-header pb-0">
                                        <h6 class="font-weight-semibold text-lg mb-0">{{__("Evolution")}}</h6>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="chart mb-2">
                                            <canvas id="chart" class="chart-canvas"
                                                    style="display: block; box-sizing: border-box;
                                            height: 440px; width: 474px;">
                                            </canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <script src="/assets/js/plugins/chartjs.min.js"></script>
                            <script>
                                ctx = document.getElementById("chart").getContext("2d");
                                chart = new Chart(ctx, {
                                    type: "line",
                                    data: {
                                        labels: {!! json_encode($charts['all']['labels'], JSON_PRETTY_PRINT)!!},
                                        datasets: [
                                            @foreach ($charts['all']['loans'] as $loanId => $datas)
                                                {
                                                    hidden: {{$datas['hidden']}},
                                                    label: "{{$datas['label']}}",
                                                    data: {!! json_encode($datas['data'], JSON_PRETTY_PRINT)!!},
                                                    hoverOffset: 4,
                                                    backgroundColor: '{{$datas['color']}}',
                                                    borderColor: '{{$datas['color']}}',
                                                    pointStyle: 'circle',
                                                    pointRadius: 5,
                                                    pointHoverRadius: 8
                                                },
                                            @endforeach
                                        ]
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
                                        scales: {
                                            x: {
                                                title: {
                                                    display: true,
                                                    text: "{{__("Date")}}"
                                                },
                                                ticks: {
                                                    autoSkip: true,
                                                    maxTicksLimit: 12
                                                }
                                            },
                                            y: {
                                                title: {
                                                    display: true,
                                                    text: "{{__("Amount")}}"
                                                },
                                                beginAtZero: true,
                                            }
                                        },
                                    }
                                });
                            </script>
                        </div>

                        <div class="row">
                            <br/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-app.footer />
    </main>
</x-app-layout>

<script src="/assets/js/plugins/datatables.js"></script>
<script src="/assets/js/plugins/datatables-override.js"></script>
<script>
    window.onload = function(e){
        let columnDefs = setColumnDefsAmount([1,2]);
        const dataTableBasic = initializeDataTable("#datatable", 1, columnDefs);

        $('#datatable-search').keyup(function () {
            dataTableBasic.search($(this).val()).draw();
        })
    };
</script>
