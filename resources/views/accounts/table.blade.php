<div class="border-bottom py-3 px-3 d-sm-flex align-items-center">
    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
        {{__('Your 1000 latest transactions')}}
    </div>

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
    <table class="table text-secondary text-center nowhitespace" id="datatable">
        <thead class="bg-gray-100">
        <tr>
            <th class="text-secondary text-xs font-weight-semibold opacity-7">
                {{__("Date")}}</th>
            <th class="text-secondary text-xs font-weight-semibold opacity-7">
                {{__("Label")}}</th>
            <th class="text-secondary text-xs font-weight-semibold opacity-7 ps-2">
                {{__('Amount')}}</th>
            <th class="text-secondary text-xs font-weight-semibold opacity-7 ps-2">
                {{__('Category')}}
            </th>
        </tr>
        </thead>
        <tbody>
            @foreach ($account->transactions as $transaction)
                <tr>
                    <td>
                        <p class="text-sm font-weight-normal mb-0">
                            {{formatDate($transaction->created_at)}}
                        </p>
                    </td>
                    <td>
                        <p class="text-sm font-weight-normal mb-0">
                            {{$transaction->name}}<br/>
                            {{$transaction->check_number}}
                            {{$transaction->note}}
                        </p>
                    </td>
                    <td class="align-middle bg-transparent border-bottom">
                        <p class="text-sm font-weight-normal mb-0">
                            {{$transaction->amount}}
                        </p>
                    </td>
                    <td>
                        <p class="text-sm font-weight-normal mb-0">
                            {{$transaction->category}}
                        </p>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>