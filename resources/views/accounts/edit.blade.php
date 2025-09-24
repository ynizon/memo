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
                                    <a href="{{ route('accounts.create') }}" class="btn btn-dark btn-primary">
                                        <i class="fas fa-plus me-2"></i> {{__("Add Account")}}
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
                            <div class="col-md-12 px-4">
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
                                                   name="archive"
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
                                            <i class="pad fas fa-save" aria-hidden="true"></i>{{__("Save")}}
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="col-md-6 px-4">
                                <ul>
                                @foreach ($account->amounts as $amount)
                                    <li>
                                        {{formatDate($amount->created_at)}} : {{$amount->amount}} €
                                        <a href="{{ route('accounts.remove_amount', ["amount_id"=>$amount->id]) }}">
                                            <i class="fa fa-delete-left"></i></a>
                                    </li>
                                @endforeach
                                </ul>
                            </div>
                        </div>
                        <hr/>
                        @include("/accounts/table", compact('account'))
                    </div>
                </div>
            </div>
        </div>

        <script src="/assets/js/plugins/datatables.js"></script>
        <script>
            window.onload = function(e){
                $('.colorpicker').colorpicker();

                const dataTableBasic = new simpleDatatables.DataTable("#datatable", {
                    searchable: false,
                    fixedHeight: true,
                    bLengthChange: false,
                    paging: true,
                    showNEntries: false,
                    perPage: 50,
                });

                $('#datatable-search').keyup(function () {
                    dataTableBasic.search($(this).val()).draw();
                })
            };
        </script>

        <x-app.footer />
    </main>

</x-app-layout>
