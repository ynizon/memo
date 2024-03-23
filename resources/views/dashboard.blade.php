<x-app-layout>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-app.navbar />
        <div class="container-fluid py-4 px-5">
            <div class="row">
                <div class="col-md-12">
                    <div class="d-md-flex align-items-center mb-3 mx-2">
                        <div class="mb-md-0 mb-3">
                            <h3 class="font-weight-bold mb-0">Hello, {{Auth::user()->name}}</h3>
                            <p class="mb-0"></p>
                        </div>
                        <a href="{{ route('tasks.create') }}" class="btn-icon d-flex align-items-center mb-0 ms-md-auto mb-sm-0 mb-2 me-2 btn btn-dark btn-primary">
                            <i class="fas fa-plus me-2"></i> {{__("Add Task")}}
                        </a>
                    </div>
                </div>
            </div>
            <hr class="my-0">

            <div class="row my-4">
                <div class="col-lg-12 col-md-12">
                    @include("/tasks/table", compact('categories', 'tasks'))
                </div>
            </div>
            <div class="row">
                @foreach ($transactions as $transaction)
                    @if ($transaction['price'] > 0)
                        <div class="col-xl-3 col-sm-6 mb-xl-0">
                            <div class="card border shadow-xs mb-4">
                                <div class="card-body text-start p-3 w-100">
                                    <div
                                        class="icon icon-shape icon-sm bg-dark text-white text-center border-radius-sm d-flex align-items-center justify-content-center mb-3">
                                        <i class="fa {{$transaction['category']->icon}}"></i>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="w-100">
                                                <p class="text-sm text-secondary mb-1">{{__("Total Expenses")}}</p>
                                                <h4 class="mb-2 font-weight-bold">{{$transaction['price']}} €</h4>
                                                <div class="d-flex align-items-center">
                                                    <span class="text-sm ms-1">{{formatDate($transaction['latest'])}}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            <x-app.footer />
        </div>
    </main>

</x-app-layout>