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
                                    <h5 class="">{{__('Users Management')}}</h5>
                                    <p class="mb-0 text-sm">

                                    </p>
                                </div>
                                <div class="col-6 text-end">
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
                                        <th>
                                            {{__("Name")}}
                                        </th>
                                        <th>
                                            {{__("Email")}}
                                        </th>
                                        <th>
                                            {{__("Admin")}}
                                        </th>
                                        <th>
                                            {{__("Premium")}}
                                        </th>
                                        <th>
                                            {{__("Action")}}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td class="align-middle bg-transparent border-bottom">
                                            {{$user->name}}
                                        </td>
                                        <td class="align-middle bg-transparent border-bottom">{{$user->email}}</td>
                                        <td class="align-middle bg-transparent border-bottom">
                                            <div class="form-check form-switch ps-0" style="display:inline-block">
                                                <input class="form-check-input ms-auto" type="checkbox" value="1"
                                                       name="admin"
                                                       onclick="toogleAdmin({{$user->id}})"
                                                       @if ($user->admin || $user->email == env("ADMIN_EMAIL")) checked @endif>
                                            </div>
                                        </td>
                                        <td class="align-middle bg-transparent border-bottom">
                                            <div class="form-check form-switch ps-0" style="display:inline-block">
                                                <input class="form-check-input ms-auto" type="checkbox" value="1"
                                                       name="premium"
                                                       onclick="tooglePremium({{$user->id}})"
                                                       @if ($user->premium) checked @endif>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle bg-transparent border-bottom">
                                            @if ($user->id != Auth::user()->id)
                                                <form action="{{ route('users.destroy', $user->id) }}" method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="nobtn"><i class="fas fa-trash" aria-hidden="true"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-app.footer />
    </main>
</x-app-layout>

<script src="/assets/js/plugins/datatables.js"></script>
<script>
    const dataTableBasic = new DataTable("#datatable", {
        "language": {
            "url": "/assets/js/fr-FR.json"
        },
        searching: true,
        fixedHeight: true,
        bLengthChange: false,
        paging: true,
        showNEntries: false,
        pageLength: 30,
    });

    $('#datatable-search').keyup(function () {
        dataTableBasic.search($(this).val()).draw();
    })

    function tooglePremium(userId){
        window.location = '/users/'+userId+'/togglePremium';
    }

    function toogleAdmin(userId){
        window.location = '/users/'+userId+'/toggleAdmin';
    }
</script>
