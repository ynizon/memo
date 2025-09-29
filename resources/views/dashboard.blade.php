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
                @foreach ($expenses as $expense)
                    @if ($expense['now'] > 0 && !$expense['category']['archive'])
                        <div class="col-xl-3 col-sm-6 mb-xl-0 px-3">
                            <div class="card border shadow-xs mb-4">
                                <div class="card-body text-start p-3 w-100">
                                    <div
                                        class="icon icon-shape icon-sm bg-dark text-white text-center border-radius-sm align-items-center justify-content-center mb-3"
                                        style="background:{{$expense['category']['color']}} !important">
                                        <i class="fa {{$expense['category']['icon']}}"></i>
                                    </div>
                                    <div class="d-inline px-2" style="font-weight: bold">
                                        <a href="{{$expense['category']['href']}}">{{__($expense['category']['name'])}} ({{currency($expense['now'])}})</a>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="w-100">
                                                <p class="text-sm text-secondary mb-1">{{$expense['category']['label']}} : {{formatDate($expense['latest'])}}</p>
                                                <div class="d-flexOLD align-items-center">
                                                    <span class="text-sm ms-0">{{__("Previous")}} : {{currency($expense['last'])}}</span>
                                                    @if ($expense['last'] > 0)
                                                        <span class="text-sm font-weight-bolder @if (round(($expense['now'] - $expense['last']) / $expense['last'] * 100) > 0)
                                                            text-success @else text-danger  @endif" style="float:right">
                                                            <i class="fa fa-chevron-@if (round(($expense['now'] - $expense['last']) / $expense['last'] * 100) > 0)
                                                                fa-chevron-up @else fa-chevron-down @endif text-xs me-1"></i>
                                                            {{round(($expense['now'] - $expense['last']) / $expense['last'] * 100) }} %
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
