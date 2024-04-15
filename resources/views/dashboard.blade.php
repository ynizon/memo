<x-app-layout>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-app.navbar />
        <div class="container-fluid py-4">
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
            <div class="row">
                @foreach ($transactions as $transaction)
                    @if ($transaction['now'] > 0 && !$transaction['category']->archive)
                        <div class="col-xl-3 col-sm-6 mb-xl-0 px-3">
                            <div class="card border shadow-xs mb-4">
                                <div class="card-body text-start p-3 w-100">
                                    <div
                                        class="icon icon-shape icon-sm bg-dark text-white text-center border-radius-sm align-items-center justify-content-center mb-3"
                                        style="background:{{$transaction['category']->color}} !important">
                                        <i class="fa {{$transaction['category']->icon}}"></i>
                                    </div>
                                    <div class="d-inline px-2" style="font-weight: bold">
                                        <a href="/tasks?category_id={{$transaction['category']->id}}">{{__($transaction['category']->name)}} ({{$transaction['now']}} €)</a>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="w-100">
                                                <p class="text-sm text-secondary mb-1">{{__("Last expense")}} : {{formatDate($transaction['latest'])}}</p>
                                                <div class="d-flexOLD align-items-center">
                                                    <span class="text-sm ms-0">{{__("Previous")}} : {{$transaction['last']}} €</span>
                                                    @if ($transaction['last'] > 0)
                                                        <span class="text-sm text-success font-weight-bolder" style="float:right">
                                                            <i class="fa fa-chevron-up text-xs me-1"></i>
                                                            {{round(($transaction['now'] - $transaction['last']) / $transaction['last'] * 100) }} %
                                                        </span>
                                                    @endif
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
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    @include("/tasks/table", compact('categories', 'tasks'))
                </div>
            </div>
            <x-app.footer />
        </div>
    </main>

</x-app-layout>
