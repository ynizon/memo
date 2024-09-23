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
                                <div class="form-group">
                                    <label for="category_id">{{__('Category')}}</label>
                                    <select type="text" class="form-control" id="category_id" name="category_id"
                                            required>
                                        @foreach($categories as $category)
                                            <option @if ($task->category_id == $category->id) selected @endif value="{{$category->id}}">{{__($category->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="name">{{__('Name')}}</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                           value="@if (old('name') != ''){{old('name')}}@else{{$task->name}}@endif" required>
                                </div>
                                @if ($task->id == 0 && Auth::user()->isPremium())
                                    <div class="form-group">
                                        <label for="name">{{__('Attachment')}}</label>
                                        <input type="file" name="file" class="form-control"/>
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label for="information">{{__('Information')}}</label>
                                    <textarea class="form-control" id="information" rows="3" name="information">@if (old('information') != ''){{old('information')}}@else{{$task->information}}@endif</textarea>
                                </div>
                                <div class="form-group">
                                    <label for="created_at">{{__('Date')}}</label>
                                    <input type="date" class="form-control" id="created_at" name="created_at"
                                           value="@if ($task->id > 0){{formatDateUK($task->created_at)}}@else{{date("Y-m-d")}}@endif">
                                </div>
                                <div class="form-group">
                                    <label for="price">{{__('Price')}}</label>
                                    <input type="text" class="form-control" id="price" name="price"
                                           value="@if (old('price') != ''){{old('price')}}@else{{$task->price}}@endif">
                                </div>

                                @if ($task->id > 0 && Auth::user()->isPremium())
                                    <br/>
                                    <hr/><br/>
                                    <div class="row">
                                        <div class="col-6">
                                            <h5 class="">{{__('Attachments')}}</h5>
                                            <p class="mb-0 text-sm">

                                            </p>
                                        </div>
                                    </div>

                                    @if (count($task->attachments) > 0 && Auth::user()->isPremium())
                                        <table class="table text-secondary">
                                            <tbody>
                                                @foreach ($task->attachments as $attachment)
                                                <tr>
                                                    <td class="align-middle bg-transparent border-bottom">
                                                        <a href="/attachments/{{$attachment->id}}" target="_blank"><i class="fa pad fa-paperclip"></i>{{$attachment->name}}</a></td>
                                                    <td>
                                                        &nbsp;
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-app.footer />
    </main>

</x-app-layout>
