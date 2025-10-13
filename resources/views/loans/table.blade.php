<div class="border-bottom py-3 px-3 d-sm-flex align-items-center">
    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
        {{__('Your 1000 latest transactions')}}&nbsp;&nbsp;&nbsp;&nbsp;
    </div>

    <form>
        <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
            <input type="date" class="form-control" id="from" name="from"
                   value="@if ($interval['from'] != ''){{formatDateUK($interval['from'])}}@endif">
        </div>

        <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
            <input type="date" class="form-control" id="to" name="to"
                   value="@if ($interval['to'] != ''){{formatDateUK($interval['to'])}}@else{{date("Y-m-d")}}@endif">

        </div>

        <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
            <input type="submit" value="{{__("Filter")}}" class="btn btn-primary mb-0">
        </div>
    </form>

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
            <th >
                {{__("Label")}}</th>
            <th>
                {{__("Date")}}</th>
            <th>
                {{__('Amount')}}</th>
            <th>
                {{__('Category')}}
            </th>
        </tr>
        </thead>
        <tbody>
            @foreach ($loan->amounts as $amount)
                @if (($interval['from'] == '' && $interval['to'] == '') || ($interval['from'] <= $transaction->created_at && $interval['to'] >= $transaction->created_at))
                    <tr>
                        <td>
                            {{$transaction->name}}<br/>
                            {{$transaction->check_number}}
                            {{$transaction->note}}
                        </td>
                        <td data-sort='YYYYMMDD'>
                            {{formatDate($transaction->created_at)}}
                        </td>
                        <td>
                            {{$transaction->amount}}
                        </td>
                        <td>
                            <span class="pointer" onclick="datatableSearch(this.innerText)">
                                {{$transaction->category}}
                            </span>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th></th>
                <th>{{__("Total page")}}
                    <br/>{{__("Total full")}}
                </th>
                <th></th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <br/><br/>
</div>
