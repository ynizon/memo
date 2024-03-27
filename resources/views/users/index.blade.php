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
                        <div class="table-responsive">
                            <table class="table text-secondary text-center">
                                <thead class="bg-gray-100">
                                <tr>
                                    <th
                                        class="text-left text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                                        {{__("Name")}}</th>
                                    <th
                                        class="text-left text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                                        {{__("Email")}}</th>
                                    <th
                                        class="text-center text-uppercase font-weight-bold bg-transparent border-bottom text-secondary">
                                        {{__("Action")}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td class="align-middle bg-transparent border-bottom">
                                            {{$user->name}}
                                        </td>
                                        <td class="align-middle bg-transparent border-bottom">{{$user->email}}</td>
                                        <td class="text-center align-middle bg-transparent border-bottom">
                                            <form action="{{ route('users.destroy', $user->id) }}" method="post">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="nobtn"><i class="fas fa-trash" aria-hidden="true"></i></button>
                                            </form>
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
    const dataTableBasic = new simpleDatatables.DataTable("#datatable-search", {
        searchable: true,
        fixedHeight: true,
        columns: [{
            select: [2, 6],
            sortable: false
        }]
    });
</script>
