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
                                    <h5 class="">{{__('Category Management')}}</h5>
                                    <p class="mb-0 text-sm">

                                    </p>
                                </div>
                                <div class="col-6 text-end">
                                    <a href="{{ route('categories.create') }}" class="btn btn-dark btn-primary d-none d-lg-inline-block">
                                        <i class="fas fa-plus me-2"></i> {{__("Add Category")}}
                                    </a>
                                    <a href="{{ route('categories.create') }}" class="btn btn-dark btn-primary d-inline-block d-lg-none">
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
                                        <th
                                            class="text-left text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                                            {{__("Picture")}}</th>
                                        <th
                                            class="text-left text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                                            {{__("Name")}}</th>
                                        <th
                                            class="text-center text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                                            {{__("Status")}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($categories as $category)
                                        <tr>
                                            <td class="align-middle bg-transparent border-bottom">
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <a href="/categories/{{$category->id}}/edit">
                                                        <i class="fa {{$category->icon}}"></i>
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="align-middle bg-transparent border-bottom">
                                                <a href="/categories/{{$category->id}}/edit">
                                                    {{__($category->name)}}
                                                </a>
                                            </td>
                                            <td class="align-middle bg-transparent border-bottom">
                                                <span class="badge badge-sm border @if ($category->archive)
                                                    border-secondary text-secondary bg-secondary @else border-success text-success bg-success @endif">
                                                    <a href="/categories/{{$category->id}}/edit">
                                                        @if ($category->archive) Archive @else OK @endif
                                                    </a>
                                                </span>
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
    window.onload = function(e){
        const dataTableBasic = new simpleDatatables.DataTable("#datatable", {
            searchable: false,
            fixedHeight: true,
            bLengthChange: false,
            paging: true,
            showNEntries: false,
            perPage: 10,
        });

        $('#datatable-search').keyup(function () {
            dataTableBasic.search($(this).val()).draw();
        })
    };
</script>
