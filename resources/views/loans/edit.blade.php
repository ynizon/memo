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
                            <div class="col-md-12 px-4">
                                <form action="@if ($loan->id > 0) {{ route('loans.update', $loan->id) }} @else {{
                                route('loans.store') }} @endif"
                                method="post">
                                    @if ($loan->id > 0)
                                        @method('PUT')
                                    @endif
                                    @csrf
                                    <input type="hidden" name="account_id" value="{{$loan->account_id}}" />
                                    <div class="form-group">
                                        <label for="name">{{__('Name')}}</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="{{$loan->name}}" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="ref">{{__('ref (code to look for in transactions)')}}</label>
                                        <input type="text" class="form-control" id="ref" name="ref"
                                               value="@if (old('ref') != ''){{old('ref')
                                               }}@else{{$loan->ref}}@endif" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="amount">{{__('Amount')}}</label>
                                        <input type="text" class="form-control" id="amount" name="amount"
                                               value="@if (old('amount') != ''){{old('amount')}}@else{{$loan->amount}}@endif" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="rate">{{__('Rate')}}</label>
                                        <input type="text" class="form-control" id="rate" name="rate"
                                               value="@if (old('rate') != ''){{old('rate')}}@else{{$loan->rate}}@endif" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="icon">{{__('Icon')}}&nbsp;&nbsp;&nbsp;
                                            <span class="fa {{$loan->icon}}" id="icon_example"></span>
                                        </label>

                                        <select class="form-control" id="icon" name="icon" required>
                                            @foreach($icons as $icon =>$iconValue)
                                                <option @if ($loan->icon == $icon) selected @endif value="{{$icon}}">{{$icon}}</option>
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
                                            value="@if (old('color') != ''){{old('color')}}@else{{$loan->color}}@endif" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="from">{{__('From')}}</label>
                                        <input type="date" class="form-control" id="from" name="from"
                                               value="@if (old('from') != ''){{old('from')}}@else{{formatDateUK
                                               ($loan->from)}}@endif" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="to">{{__('To')}}</label>
                                        <input type="date" class="form-control" id="to" name="to"
                                               value="@if (old('to') != ''){{old('to')}}@else{{formatDateUK
                                               ($loan->to)}}@endif" required>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-check form-switch ps-0">
                                            <input class="form-check-input ms-auto" type="checkbox" value="1"
                                                   name="active"
                                                   id="flexSwitchCheckDefault2" @if ($loan->active) checked @endif>
                                            <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                                   for="flexSwitchCheckDefault2">{{__("Active")}}</label>
                                        </div>
                                    </div>
                                    <br>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="pad fas fa-save" aria-hidden="true"></i>{{__("Save")}}
                                    </button>
                                </form>
                                @if ($loan->id > 0)
                                <form action="{{ route('loans.destroy', $loan->id) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger float-end"><i class="pad fas fa-trash" aria-hidden="true"></i>
                                        {{__("Delete")}}</button>
                                </form>
                                @endif
                            </div>
                        </div>
                        <hr/>

                        <div class="row">
                            <div class="col-lg-12 col-md-12 mb-md-0 mb-4">
                                <div class="card shadow-xs border h-100">
                                    <div class="card-header pb-0">
                                        <h6 class="font-weight-semibold text-lg mb-0">{{$loan->name}}</h6>
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
                                            @foreach ($charts['loans'] as $loanId => $datas)
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
                                                beginAtZero: true,
                                            },
                                        },
                                    }
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-app.footer />
    </main>

</x-app-layout>
