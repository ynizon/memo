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
                                    <h5 class="">{{__('Account Management')}}</h5>
                                    <p class="mb-0 text-sm">

                                    </p>
                                </div>
                                <div class="col-6 text-end">
                                    <a href="{{ route('loans.create', ["acid" => $account->id]) }}" class="btn btn-dark
                                    btn-primary">
                                        <i class="fas fa-plus me-2"></i> {{__("Add Loan")}}
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

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-9 px-4">
                                <form action="@if ($account->id > 0) {{ route('accounts.update', $account->id) }} @else {{ route('accounts.store') }} @endif"
                                method="post">
                                    @if ($account->id > 0)
                                        @method('PUT')
                                    @endif
                                    @csrf
                                    <div class="form-group">
                                        <label for="name">{{__('Name')}}</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="@if (old('name') != ''){{old('name')}}@else{{$account->name}}@endif" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="icon">{{__('Icon')}}&nbsp;&nbsp;&nbsp;
                                            <span class="fa {{$account->icon}}" id="icon_example"></span>
                                        </label>

                                        <select class="form-control" id="icon" name="icon" required>
                                            @foreach($icons as $icon =>$iconValue)
                                                <option @if ($account->icon == $icon) selected @endif value="{{$icon}}">{{$icon}}</option>
                                            @endforeach
                                        </select>

                                        <script>
                                            document.getElementById('icon').addEventListener('change', function() {
                                                $("#icon_example").attr("class","fa "+this.value);
                                            });
                                        </script>
                                    </div>
                                    <div class="form-group">
                                        <label for="color">{{__('Color code')}}</label>
                                        <input type="text" class="colorpicker form-control" id="color" name="color"
                                            value="@if (old('color') != ''){{old('color')}}@else{{$account->color}}@endif" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="position">{{__('Position')}}</label>
                                        <input type="text" class="form-control" id="position" name="position"
                                               value="@if (old('position') != ''){{old('position')}}@else{{$account->position}}@endif" required>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-check form-switch ps-0">
                                            <input class="form-check-input ms-auto" type="checkbox" value="1"
                                                   name="active"
                                                   id="flexSwitchCheckDefault2" @if ($account->active) checked @endif>
                                            <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                                   for="flexSwitchCheckDefault2">{{__("Active")}}</label>
                                        </div>
                                    </div>
                                    <br>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="pad fas fa-save" aria-hidden="true"></i>{{__("Save")}}
                                    </button>
                                </form>
                                @if ($account->id > 0)
                                <form action="{{ route('accounts.destroy', $account->id) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger float-end"><i class="pad fas fa-trash" aria-hidden="true"></i>
                                        {{__("Delete")}}</button>
                                </form>
                                @endif
                            </div>
                            <div class="col-md-3 px-4">
                                <ul class="text-end px-4" style="list-style: none">
                                    @foreach ($account->loans as $loan)
                                        <li><a href="/loans/{{$loan->id}}/edit"><i
                                                        class="fa {{$loan->icon}}"></i>&nbsp;{{$loan->name}}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <hr/>
                        <div class="row">
                            <div class="col-md-6 px-4">
                                @if ($account->id > 0)
                                    <form action="{{ route('accounts.add_amount', ["account_id"=>$account->id]) }}"
                                          method="post">
                                        <h5>{{__("Add amount at date")}}</h5>
                                        @csrf
                                        <div class="form-group">
                                            <label for="date">{{__('Date')}}</label>
                                            <input type="date" class="form-control" id="created_at" name="created_at"
                                                   value="{{date("Y-m-d")}}">
                                        </div>

                                        <div class="form-group">
                                            <label for="amount">{{__('Amount')}}</label>
                                            <input type="text" class="form-control" id="amount" name="amount"
                                                   value="" required>
                                        </div>
                                        <br>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="pad fas fa-save" aria-hidden="true"></i>{{__("Add")}}
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="col-md-6 px-4">
                                <ul>
                                @foreach ($account->amounts as $amount)
                                    @if (!$amount->calculated)
                                        <li>
                                            {{formatDate($amount->created_at)}} : {{currency($amount->amount)}}
                                            <a href="{{ route('accounts.remove_amount', ["amount_id"=>$amount->id]) }}">
                                                <i class="fa fa-delete-left"></i></a>
                                        </li>
                                    @endif
                                @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12 col-md-12 mb-md-0 mb-4">
                                <div class="card shadow-xs border h-100">
                                    <div class="card-header pb-0">
                                        <h6 class="font-weight-semibold text-lg mb-0">{{$account->name}}</h6>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="chart mb-2">
                                            <canvas id="chart" class="chart-canvas"
                                                    height="240" style="display: block; box-sizing: border-box;
                                            height: 240px; width: 474px;" width="474">
                                            </canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <script src="/assets/js/plugins/chartjs.min.js"></script>
                            <script>
                                let ctx = document.getElementById("chart").getContext("2d");
                                let chart = new Chart(ctx, {
                                    type: "line",
                                    data: {
                                        labels: {!! json_encode($charts['labels'], JSON_PRETTY_PRINT)!!},
                                        datasets: [
                                            @foreach ($charts['accounts'] as $accountId => $datas)
                                            {
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
                                        ],
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: false,
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
                                            }
                                        },
                                    }
                                });
                            </script>
                        </div>
                        <hr/>
                        @include("/accounts/table", compact('account', 'interval'))
                    </div>
                </div>
            </div>
        </div>

        <script src="/assets/js/plugins/datatables.js"></script>
        <script src="/assets/js/plugins/datatables-override.js"></script>
        <script>
            window.onload = function(e){
                $('.colorpicker').colorpicker();

                let columnDefs = setColumnDefsAmount(2);
                let dataTableBasic = initializeDataTable("#datatable", 2, columnDefs);

                $('#datatable-search').keyup(function () {
                    dataTableBasic.search($(this).val()).draw();
                })

                dataTableBasic.on('init.dt', function() {
                    //Order by date
                    $('.dt-column-title').eq(1).trigger('click');
                });
            };
        </script>

        <x-app.footer />
    </main>

</x-app-layout>
