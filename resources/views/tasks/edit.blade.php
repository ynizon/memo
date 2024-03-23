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
                                    <h5 class="">{{__('Task Management')}}</h5>
                                    <p class="mb-0 text-sm">

                                    </p>
                                </div>
                                <div class="col-6 text-end">
                                    <a href="{{ route('tasks.create') }}" class="btn btn-dark btn-primary">
                                        <i class="fas fa-plus me-2"></i> {{__("Add Task")}}
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
                                <form action="@if ($task->id > 0) {{ route('tasks.update', $task->id) }} @else {{ route('tasks.store') }} @endif"
                                method="post">
                                    @if ($task->id > 0)
                                        @method('PUT')
                                    @endif
                                    @csrf
                                    <div class="form-group">
                                        <label for="title">{{__('Category')}}</label>
                                        <select type="text" class="form-control" id="category_id" name="category_id"
                                                required>
                                            @foreach($categories as $category)
                                                <option value="{{$category->id}}">{{$category->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="name">{{__('Name')}}</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="{{$task->name}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="information">{{__('Information')}}</label>
                                        <textarea class="form-control" id="information" rows="3" name="information">{{$task->information}}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="price">{{__('Price')}}</label>
                                        <input type="text" class="form-control" id="price" name="price"
                                               value="{{$task->price}}">
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
        <x-app.footer />
    </main>

</x-app-layout>