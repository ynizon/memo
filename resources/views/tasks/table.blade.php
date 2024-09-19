<div class="border-bottom py-3 px-3 d-sm-flex align-items-center">
    @if ($groupId == 0)
        <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
            <input type="radio" class="btn-check" name="btnradiotable" id="btnradiotableX" autocomplete="off" @if ($categoryId == 0) checked="" @endif>
            <label class="btn btn-white px-3 mb-0 datafiltreCategory" data-chart="*" data-category="" for="btnradiotableX">{{__("All")}}</label>
            @foreach($categories as $category)
                <input type="radio" class="btn-check" name="btnradiotable" id="btnradiotable{{$loop->index}}" autocomplete="off"  @if ($categoryId == $category->id) checked="" @endif>
                <label class="btn btn-white px-3 mb-0 datafiltreCategory" data-chart="{{$loop->index}}" data-category="{{$category->id}}" for="btnradiotable{{$loop->index}}"><i class="fa {{$category->icon}}"></i></label>
            @endforeach
        </div>
    @endif
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
            <th class="text-secondary text-xs font-weight-semibold opacity-7">
                {{__('Name')}}</th>
            <th class="text-secondary text-xs font-weight-semibold opacity-7 ps-2">
                {{__('Amount')}}</th>
            <th class="text-secondary text-xs font-weight-semibold opacity-7 ps-2">
                {{__('Date')}}
            </th>
            <th class="text-secondary text-xs font-weight-semibold opacity-7 ps-2 d-none d-md-table-cell">
                {{__('Information')}}</th>
        </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
                <tr>
                    <td>
                        <div class="d-flex px-2">
                            <div class="rounded-circle bg-gray-100 me-2 my-2">
                                <a href="/tasks/{{$task->id}}/edit">
                                    <i class="pad fa fa-list {{$task->category->icon}}"></i>
                                </a>
                                <span class="d-none">Category-{{$task->category->id}}</span>
                            </div>
                            <div class="my-auto">
                                @if ($task->user_id == Auth::user()->id)
                                    <a href="/tasks/{{$task->id}}/edit" class="list_task_item">
                                        <h6 class="mb-0 text-sm">
                                            @if ($task->reminder)
                                                <svg height="22" width="22" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="padding-right:5px"
                                                     fill="currentColor" class="cursor-pointers">
                                                    <path fill-rule="evenodd"
                                                          d="M5.25 9a6.75 6.75 0 0113.5 0v.75c0 2.123.8 4.057 2.118 5.52a.75.75 0 01-.297 1.206c-1.544.57-3.16.99-4.831 1.243a3.75 3.75 0 11-7.48 0 24.585 24.585 0 01-4.831-1.244.75.75 0 01-.298-1.205A8.217 8.217 0 005.25 9.75V9zm4.502 8.9a2.25 2.25 0 104.496 0 25.057 25.057 0 01-4.496 0z"
                                                          clip-rule="evenodd" />
                                                </svg>&nbsp;
                                            @endif
                                            {{$task->name}}
                                        </h6>
                                    </a>
                                    @if (count($task->attachments) > 0)
                                        <ul class="docs">
                                        @foreach ($task->attachments as $attachment)
                                                <li><a href="/attachments/{{$attachment->id}}" target="_blank">
                                                        <i class="fa pad fa-paperclip"></i>{{$attachment->name}}
                                                    </a>
                                                </li>
                                        @endforeach
                                        </ul>
                                    @endif
                                @else
                                    <h6 class="mb-0 text-sm">
                                        {{$task->name}}
                                    </h6>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="text-sm font-weight-normal mb-0">
                            @if ($task->user_id == Auth::user()->id)
                                <a href="/tasks/{{$task->id}}/edit">
                                    @if ($task->price > 0){{$task->price}} €@endif
                                </a>
                            @else
                                @if ($task->price > 0){{$task->price}} €@endif
                            @endif
                        </p>
                    </td>
                    <td>
                        <span class="text-sm font-weight-normal">
                            @if ($task->user_id == Auth::user()->id)
                                <a href="/tasks/{{$task->id}}/edit">
                                    {{formatDate($task->created_at)}}
                                 </a>
                            @else
                                {{formatDate($task->created_at)}}
                            @endif
                        </span>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <span class="text-sm font-weight-normal">
                            @if ($task->user_id == Auth::user()->id)
                                <a href="/tasks/{{$task->id}}/edit">
                                    {{$task->information}}
                                 </a>
                            @else
                                {{$task->information}}
                            @endif
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

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

        $('.datafiltreCategory').click(function () {
            dataTableBasic.search('Category-' + $(this).attr('data-category'));
            if (chart){
                let chartColumn = $(this).attr('data-chart');
                chart.data.datasets.forEach(function(ds) {
                    ds.hidden = !(chartColumn === '*');
                });
                if (chartColumn !== '*'){
                    chart.data.datasets[chartColumn].hidden = false;
                }

                chart.update();
            }
        })

        @if ($categoryId > 0)
            try {
                $("[data-category='{{$categoryId}}']").click();
            } catch(Exception){
              //Nothing
            }
        @endif
    };
</script>
