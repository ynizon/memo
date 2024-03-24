<x-app-layout>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-app.navbar />
        <div class="px-5 py-4 container-fluid">
            <div class="mt-4 row">
                <div class="col-12">
                    <div class="card">
                        <div class="pb-0 card-header">
                            <div class="row">
                                <div class="col-6">
                                    <h5 class="">{{__('Category Management')}}</h5>
                                    <p class="mb-0 text-sm">

                                    </p>
                                </div>
                                <div class="col-6 text-end">
                                    <a href="{{ route('categories.create') }}" class="btn btn-dark btn-primary">
                                        <i class="fas fa-plus me-2"></i> {{__("Add Category")}}
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
                                <form action="@if ($category->id > 0) {{ route('categories.update', $category->id) }} @else {{ route('categories.store') }} @endif"
                                method="post">
                                    @if ($category->id > 0)
                                        @method('PUT')
                                    @endif
                                    @csrf
                                    <div class="form-group">
                                        <label for="title">{{__('Name')}}</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="{{$category->name}}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="icon">{{__('Icon')}} (Font awesome class)&nbsp;&nbsp;
                                        <span class="fa fa-list" id="icon_example"></span>
                                        </label>

                                        <select class="form-control" id="icon" name="icon" required>
                                            @foreach($icons as $icon =>$iconValue)
                                                <option @if ($category->icon == $icon) selected @endif value="{{$icon}}">{{$icon}}</option>
                                            @endforeach
                                        </select>

                                        <script>
                                            document.getElementById('icon').addEventListener('change', function() {
                                                $("#icon_example").attr("class","fa "+this.value);
                                            });
                                        </script>
                                    </div>
                                    <div class="form-group">
                                        <label for="color">{{__('Color')}} (Css code: #FF0000)</label>
                                        <input type="text" class="colorpicker form-control" id="color" name="color"
                                            value="{{$category->color}}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="price">{{__('Number of months of historic')}}</label>
                                        <select class="form-control" id="month" name="month">
                                            @for($month = 0; $month <= 36; $month++)
                                                <option @if ($category->month == $month) selected @endif value="{{$month}}">{{$month}}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <div class="form-check form-switch ps-0">
                                            <input class="form-check-input ms-auto" type="checkbox" value="1"
                                                   name="archive"
                                                   id="flexSwitchCheckDefault2" @if ($category->archive) checked @endif>
                                            <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                                   for="flexSwitchCheckDefault2">{{__("Archive")}}</label>
                                        </div>
                                    </div>
                                    <br>
                                    <button type="submit" class="btn btn-primary">{{__("Save")}}</button>
                                </form>
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
        <x-app.footer />
    </main>

</x-app-layout>
