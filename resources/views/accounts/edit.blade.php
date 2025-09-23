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
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.onload = function(e) {
                $('.colorpicker').colorpicker();
            };
        </script>

        <div>
            <div class="table-responsive">
                <table class="table text-secondary text-center" id="datatable">
                    <thead class="bg-gray-100">
                    <tr>
                        <th
                            class="text-left text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                            {{__("Label")}}
                        </th>
                        <th
                            class="text-left text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                            {{__("Amount")}}
                        </th>
                        <th
                            class="text-left text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                            {{__("Category")}}
                        </th>
                        <th
                            class="text-left text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                            {{__("Check")}}
                            {{__("Note")}}
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach ($account->transactions as $transaction)
                            <tr>
                                <td>{{$transaction->name}}</td>
                                <td>{{$transaction->amount}} €</td>
                                <td>{{$transaction->category}}</td>
                                <td>
                                    {{$transaction->check_number}}
                                    {{$transaction->note}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <x-app.footer />
    </main>

</x-app-layout>
